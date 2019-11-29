<?php
namespace modules\productManager\models\db;
use Yii;
use Yii\helpers\ArrayHelper;
use yii\db\PdoValue;
use yii\db\Expression;
use yii\helpers\Html;
use yii\validators\ImageValidator;
use yii\web\UploadedFile;

use yiiforces\base\ImageResize;

class Product extends \yiiforces\base\ActiveRecord
{
    public $id_category;

    protected $_prevStep;
    protected $_nextStep;

    protected $scenario;
    //images save
    protected $defaultImage;
    protected $webUpload;
    protected $webRootUpload;

    // products tables relations
    protected $_productTypeAttributes   = null;
    protected $_productCustomAttributes = null;

    const SCENARIO_STEP1        = 1; // datos basicos del producto
    const SCENARIO_STEP2        = 2; // atributos de productos por tipo producto
    const SCENARIO_STEP3        = 3;

    const MAX_SCENARIO_STEPS    = 3;

    public function init()
    {
        parent::init();

        $this->defaultImage = Yii::$app->config->get(
            'products.main.noImage',
            '@web/assets/images/not-image.jpg',
            'imagen por defecto para productos sin imagen principal'
        );

        $this->webUpload = Yii::$app->config->get(
            'products.main.webUpload',
            '@web/uploads/img/products/main-image',
            '@web path imagen principal'
        );

        $this->webRootUpload = Yii::$app->config->get(
            'products.main.webRootUpload',
            '@webroot/uploads/img/products/main-image',
            '@webroot path imagen principal',
        );
    }

    public function __get($attr)
    {
        // find dinamic input
        // buscar un input dinamico para la vista view_product_type_attributes:
        if(preg_match('<^((common_type_attribute_)+(\d+))$>', $attr))
        {
            foreach($this->getProductTypeAttributes() as $input)
            {
                if($input->name == $attr)
                    return $input->value;
            }

            return null;
        }

        return parent::__get($attr);
    }

    public function __set($attr, $value)
    {   // set dinamic value
        // setear un valor dinamico para tabla product_values:
        if(preg_match('<^((common_type_attribute_)+(\d+))$>', $attr))
        {
            foreach($this->getProductTypeAttributes() as $input)
            {
                if($input->name == $attr)
                {
                    $input->value = $value;
                    return;
                }
            }

            return;
        }

        return parent::__set($attr, $value);
    }

    public static function tableName()
    {
        return
            '{{%product}}';
    }

    public function setScenario($step)
    {
        if(is_null($step))
        {
            if($this->tmp_record_last_step + 1 > static::MAX_SCENARIO_STEPS)
                $this->scenario = static::MAX_SCENARIO_STEPS;
            else
                $this->scenario = $this->tmp_record_last_step + 1;
        }
        else{
            if($step < 2)
                $this->scenario = static::SCENARIO_STEP1;
            else
                $this->scenario = ($step <= static::MAX_SCENARIO_STEPS) ? $step : $this->tmp_record_last_step +1;
        }

        $this->scenario > static::MAX_SCENARIO_STEPS ? static::MAX_SCENARIO_STEPS : $this->scenario;

        $this->_prevStep = ($step-1 <= 0) ? null : $step-1;
        $this->_nextStep = ($step+1 >= static::MAX_SCENARIO_STEPS) ? null : $step+1;

        // si no esta guardado el step actual, resetar los valores por defecto de la db
        if($this->scenario > $this->tmp_record_last_step)
            $this->cleanActiveAttributes();
    }

    public function getScenario()
    {
        if(is_null($this->scenario))
            $this->setScenario(null);

        return $this->scenario;
    }

    protected function listImageResize()
    {
        return [
            '128' => [
                'x' => 128,
                'y' => 128,
                'q' => 100,
            ],
            '256' => [
                'x' => 256,
                'y' => 256,
                'q' => 100,
            ],
            '400' => [
                'x' => 400,
                'y' => 400,
                'q' => 100,
            ],
        ];
    }

    public function getFormTitle()
    {
        $name = ($this->is_tmp_record) ? 'Nuevo producto' : Html::encode($model->name);
        $step = ($this->is_tmp_record) ? $this->scenario . ' / ' . static::MAX_SCENARIO_STEPS : null;
        switch($this->scenario)
        {
            case static::SCENARIO_STEP1:
                return Yii::t('yiiforces', '{0} - Datos basicos {1}', [
                    $name,
                    $step
                ]);
                break;

            case static::SCENARIO_STEP2:
                return Yii::t('yiiforces', '{0} - Attributos de tipo {1}', [
                    $name,
                    $step
                ]);
                break;

            case static::SCENARIO_STEP3:
                return Yii::t('yiiforces', '{0} - Galería de imagenes {1}', [
                    $name,
                    $step
                ]);
                break;
        }
    }

    public function afterFind()
    {
        parent::afterFind();

        if(!empty($this->categories))
            $this->id_category = $this->categories[0];

        $this->setProductTypeAttributes();
    }

    public function getProductTypeAttributes()
    {
        if(is_null($this->_productTypeAttributes))
            $this->setProductTypeAttributes();

        return $this->_productTypeAttributes;
    }

    protected function setProductTypeAttributes()
    {
        if($this->isNewRecord)
            return;

        $command =  $this->getDb()->createCommand('select * from view_product_type_attributes where id_product =:id', [
            ':id' => $this->id
        ]);

        $this->_productTypeAttributes = ArrayHelper::index($command->queryAll(\PDO::FETCH_OBJ), 'id');
    }

    public function cleanActiveAttributes()
    {
        foreach($this->activeAttributes() as $attr)
            $this->{$attr} = null;
    }

    public function rules()
    {
        // clean error Unknown scenario
        $rules = [
            [['id_updated_by', 'id_created_by'], 'default', 'value' => static::getUserId(), 'on' => $this->scenario],
        ];

        if($this->scenario == static::SCENARIO_STEP1)
        {
            $rules = [
                [['id_product_type', 'id_product_manufacturer', 'id_category', 'name'], 'required', 'on' => $this->scenario],
                [['name'], 'string', 'max' => 100, 'on' => $this->scenario],
                [['code_model', 'code_barcode'], 'validateUnique', 'on' => $this->scenario],
                [['is_active'], 'default', 'value' => false, 'on' => $this->scenario],
                [['is_active'], 'boolean', 'on' => $this->scenario],
                [['manufacturer_warranty', 'store_warranty'], 'string', 'max' => 40, 'on' => $this->scenario],
                [['cost_price', 'sale_price', 'und_stock'], 'required', 'on' => $this->scenario],
                [['cost_price', 'sale_price'], 'number', 'min' => 0.1, 'on' => $this->scenario],
                [['und_stock'], 'integer', 'min' => 0, 'on' => $this->scenario],

                //@todo add whenClient
                [['is_active_color'], 'default', 'value' => false, 'on' => $this->scenario],
                [['is_active_color'], 'boolean', 'on' => $this->scenario],
                [['code_model', 'code_barcode', 'color_name'], 'string', 'max' => 20, 'on' => $this->scenario],
                [['color_code'], 'string', 'max' => 9, 'on' => $this->scenario],
                [['color_name', 'color_code'], 'required', 'when' => function($model, $attr){ return ($model->is_active_color == true); }, 'enableClientValidation'=> false , 'on' => $this->scenario],

                //@todo add whenClient
                [['is_active_size'], 'default', 'value' => false, 'on' => $this->scenario],
                [['is_active_size'], 'boolean', 'on' => $this->scenario],
                [['size_und'],  'string', 'max' => 10, 'on' => $this->scenario],
                [['size_width', 'size_long', 'size_depth'], 'number', 'min'=> '0.1', 'on' => $this->scenario],
                [['size_width', 'size_long', 'size_depth', 'size_und'], 'required', 'when' => function($model, $attr){ return ($model->is_active_size == true); }, 'enableClientValidation'=> false , 'on' => $this->scenario],

                //@todo add whenClient
                [['is_active_weight'], 'default', 'value' => false, 'on' => $this->scenario],
                [['is_active_weight'], 'boolean', 'on' => $this->scenario],
                [['weight_und'],   'string', 'max' => 10, 'on' => $this->scenario],
                [['weight_value'], 'number', 'min'=> '0.1' , 'on' => $this->scenario],
                [['weight_und', 'weight_value'], 'required', 'when' => function($model, $attr){ return ($model->is_active_weight == true); }, 'enableClientValidation'=> false , 'on' => $this->scenario],

                //@todo add whenClient

                [['main_image'] , 'required', 'when' => function($model, $attr){return $model->hasDefaultImage(); }, 'enableClientValidation'=> false],
                [['main_image'] , ImageValidator::className() , 'minWidth' => 400, 'minHeight' => 400],
            ];
        }

        // dinamic imputs attributes
        if($this->scenario == static::SCENARIO_STEP2)
        {
            foreach($this->getProductTypeAttributes(true) as $input)
            {
                if($input->is_required)
                    $rules[] = [$input->name, 'required', 'on' => $this->scenario];

                if($input->is_unique)
                    $rules[] = [$input->name, 'ckUniqueTypeAttribute', 'on' => $this->scenario];

                switch($input->datatype)
                {
                    case 'boolean':
                        $rules[] = [$input->name, 'default', 'value' => false, 'on' => $this->scenario];
                        $rules[] = [$input->name, 'boolean', 'on' => $this->scenario];
                        break;

                    case 'unsigned-integer':
                        $rules[] = [$input->name, 'integer', 'min'=> 0, 'on' => $this->scenario];
                        break;

                    case 'integer':
                        $rules[] = [$input->name, 'integer', 'on' => $this->scenario];
                        break;

                    case 'url':
                        $rules[] = [$input->name, 'url', 'on' => $this->scenario];
                        break;

                    case 'date':
                        $rules[] = [$input->name, 'date', 'format' => 'php:Y-m-d', 'on' => $this->scenario,];
                        break;

                    default:
                        $rules[] = [$input->name, 'string', 'max'=> 255, 'on' => $this->scenario];
                        break;
                }
            }
        }

        return $rules;
    }

    /*
        @todo add efective suport psql unique for attributes...
    */
    public function ckUniqueTypeAttribute($attribute)
    {
    }

    final public function save($runValidation = true, $attributeNames = null)
    {
        if($this->isNewRecord)
        {
            $this->id_created_by = static::getUserId();
            $this->id_updated_by = static::getUserId();
            $this->is_tmp_record = true;

            $status = parent::save(false, [
                'id_created_by',
                'id_updated_by',
                'is_tmp_record',
            ]);

            if($status == false)
                return false;

            $this->refresh();
            return true;
        }

        if($this->validate() == false)
            return false;

        $transaction = $this->getDb()->beginTransaction();
        $attributes  = $this->activeAttributes();

        if( in_array('id_category', $attributes) )
        {
            $this->categories = [$this->id_category];
            array_push($attributes, 'categories');
        }

        $this->tmp_record_last_step = ($this->tmp_record_last_step < $this->scenario) ? $this->scenario : $this->tmp_record_last_step;
        array_push($attributes, 'tmp_record_last_step');

        if($this->_nextStep == null)
        {
            array_push($attributes, 'updated_at');
            array_push($attributes, 'created_at');
            $this->created_at = new Expression('now()');
            $this->updated_at = new Expression('now()');
        }

        $status = parent::save(true, $attributes);
        if($status == false)
        {
            $transaction->rollback();
            return false;
        }

        if($this->scenario == static::SCENARIO_STEP2)
        {
            foreach($this->getProductTypeAttributes() as $input)
            {
                $this->getDb()->createCommand()->update(
                    '{{%product_values}}',
                    ['value' =>$input->value],
                    'id = ' . $input->id
                )->execute();
            }
        }

        //@aqui otras tablas relacionadas (por esenarios)...
        $transaction->commit();
        return true;
    }

    public static function getTmpRecord()
    {
        $userId = static::getUserId();
        $model  = static::find()->where([
            'id_created_by' => new PdoVAlue($userId, \PDO::PARAM_INT),
            'is_tmp_record' => true,
        ])->orderBy('id desc')->one();

        if(!is_null($model))
            return $model;

        $model = new static();
        $model->save();
        return $model;
    }

    public static function cancel()
    {
        static::deleteAll([
            'id_created_by' => new PdoValue(static::getUserId(), \PDO::PARAM_INT),
            'is_tmp_record' => true,
        ]);

        // @todo add drop files (images)
    }

    public function attributeLabels()
    {
        $labels = [
            'id'                        => Yii::t('yiiforces', 'ID'),
            'name'                      => Yii::t('yiiforces', 'Nombre de producto'),
            'id_product_type'           => Yii::t('yiiforces', 'Tipo de producto'),
            'id_category'               => Yii::t('yiiforces', 'Categoria'),
            'id_product_manufacturer'   => Yii::t('yiiforces', 'Fabricante (marca)'),
            'code_model'                => Yii::t('yiiforces', 'Modelo'),
            'code_barcode'              => Yii::t('yiiforces', 'Código de barras'),
            'color_name'                => Yii::t('yiiforces', 'Nombre color'),
            'color_code'                => Yii::t('yiiforces', 'Código color'),
            'manufacturer_warranty'     => Yii::t('yiiforces', 'Tiempo de garantia del fabricante'),
            'store_warranty'            => Yii::t('yiiforces', 'Tiempo de garantia en tienda'),
            'is_active'                 => Yii::t('yiiforces', ' ¿Producto activo?'),
            'size_width'                => Yii::t('yiiforces', 'Ancho'),
            'size_long'                 => Yii::t('yiiforces', 'Altura'),
            'size_depth'                => Yii::t('yiiforces', 'Produndidad'),
            'size_und'                  => Yii::t('yiiforces', 'Unidad Medida'),
            'weight_value'              => Yii::t('yiiforces', 'Peso'),
            'weight_und'                => Yii::t('yiiforces', 'Unidad Medida'),
            'cost_price'                => Yii::t('yiiforces', 'Precio de compra'),
            'sale_price'                => Yii::t('yiiforces', 'Porcentaje de ganacia'),
            'und_stock'                 => Yii::t('yiiforces', 'Cantidad de existencia'),
            'image'                     => Yii::t('yiiforces', 'Imagen principal'),

            'is_active_color'           => Yii::t('yiiforces', '¿Esta activa la definición de {0} ?', ['color']),
            'is_active_size'            => Yii::t('yiiforces', '¿Esta activa la definición de {0} ?', ['dimensiones']),
            'is_active_weight'          => Yii::t('yiiforces', '¿Esta activa la definición de {0} ?', ['peso']),
        ];
        // dinamic attributes labels
        foreach($this->getProductTypeAttributes() as $input)
            $labels[$input->name] = HTml::encode($input->label);

        return $labels;
    }

    // relations
    public function getProductType()
    {
        return ProductType::find()->where('id=:id',[
            ':id' => $this->id_product_type
        ])->one();
    }

    // maps select2:
    public function getListProductType()
    {
        $list = ProductType::find()->where('id=:id',[
            ':id' => $this->id_product_type
        ])->asArray()->all();

        return ArrayHelper::map($list, 'id', 'name');
    }

    public function getListCategories()
    {
        $param = [$this->id_category];

        $list = ProductCategory::find()->andFilterWhere([
            'in', 'id', $param
        ])->asArray()->all();

        return ArrayHelper::map($list, 'id', 'name');
    }

    public function getListProductManufacturer()
    {
        $list = ProductManufacturer::find()->where('id=:id',[
            ':id' => $this->id_product_manufacturer
        ])->asArray()->all();

        return ArrayHelper::map($list, 'id', 'name');
    }

    public function hasSavedScenario()
    {
        return
            ($this->tmp_record_last_step >= $this->scenario);
    }

    // images
    public function getImage($size = null)
    {
        if($this->isNewRecord || is_null($this->main_image))
            return $this->getDefaultImage();

        $list = $this->listImageResize();
        $size = isset($list[$size]) ? $size : '400';
        $path = '/' . $list[$size]['x'] . 'x' . $list[$size]['y'] . '/';
        $file = $this->webRootUpload . $path . $this->main_image;
        $file = Yii::getAlias($file);

        if(!is_file($file) || !is_readable($file))
            return $this->getDefaultImage();
        else
           return Yii::getAlias($this->webUpload . $path . $this->main_image) . '?t=' . time();
    }

    public function getDefaultImage()
    {
        return
            Yii::getAlias($this->defaultImage);
    }

    public function hasDefaultImage()
    {
        return
            ( $this->getImage() == $this->getDefaultImage() );
    }


    public function isEndStep()
    {
        return
            ($this->scenario >= static::MAX_SCENARIO_STEPS);
    }

    public function isFirstStep()
    {
        return
            ($this->scenario == static::SCENARIO_STEP1);
    }

    public function isActiveBtnCancel()
    {
        return
            (($this->tmp_record_last_step > 0) ? true : false);
    }

    public function getLastStep()
    {
        $step = $this->scenario > 1 ? $this->scenario -1 : 0;
        return $step;
    }

    public function getNextStep()
    {
        if($this->scenario < static::MAX_SCENARIO_STEPS)
            return $this->scenario + 1;
        else
            return 0;
    }
}
