<?php
namespace models;

use Yii;

/*

If there is something that bothers me about the use of
fields () within the model is what it should be called to obtain a value.

A trivial example is that of documentation:
ps: //www.yiiframework.com/doc/guide/2.0/en/structure-models#fields
If one wishes to use a value of fields
you should call it this way
$model->toArray(['name'])['name'];
Doing this is annoying, it's not like using relationships
With the getters. But it is what exists.
I was analyzing how to directly use the object operator from an index of some fields
something like
$model->name (following the example of the doc link)
After thinking for a while, it occurred to me to overwrite the __get of yii\base\BaseObject
to reset an object of the stdObject class

I still have no idea what negative impact my overwriting has.
What do you think about it?

I leave the code in my git in case you want to see it.


    Overload __get($name) to get some value from an index in fields with the object notation

    Example use:
    $model = new XModel();
    $model->fullName == $model->toArray(['fullName'])['fullName']
    $model->user->id == $model->toArray(['user'])['user']['id']
    $model->anyArray->z->z3->z31->a3 == $model->toArray(['anyArray'])['anyArray']['z']['z3']['z31']['a3'] == 84484;

*/

class XModel extends \Yii\base\Model
{
    public $first_name;
    public $last_name;

    public function init()
    {
        parent::init();
    }


    public function fields()
    {
        return [
            'user' =>function(){
                return Yii::$app->user->identity; // example for ActiveRecord Model
            },
            'fullName' => function($model){
                return $model->first_name . ' ' . $model->lastName;
            },
            'anyArray' => function(){
                return [
                    'x' => 1,
                    'y' => 2,
                    'z' => [
                        'z1' => 'a' ,
                        'z2' => 'b' ,
                        'z3' => [
                            'z31' => [
                                'a1' => 55484,
                                'a2' => 94894,
                                'a3' => 84484
                            ]
                        ]
                    ]
                ];
            }
        ];
    }


    public function __get($name)
    {
        $keysFields = array_keys($this->fields());

        if($this->canGetProperty($name) || !in_array($name, $keysFields) )
            return parent::__get($name);

        $fieldIndex  = $this->toArray([$name], [$name], true);

        if(!is_array($fieldIndex[$name]))
            return $fieldIndex[$name];

        // convert array key field to object recursive:
        $fn2Object = function ($node) use (&$fn2Object) {

            $stdObj = new \stdClass();

            foreach ($node as $key => $value)
            {
                if (is_array($value))
                    $value = $fn2Object($value);

                $stdObj->$key = $value;
            }

            return $stdObj;
        };

        return $fn2Object($fieldIndex);
    }
}




