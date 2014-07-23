<?php

namespace lib\MovieRepo;

class Factory
{
    public static function create($name)
    {
        $class = __NAMESPACE__ . '\\' . $name;
        if (!class_exists($class))
        {
            Throw new \Exception("Parser not exists ($name)");
        }

        return new $class;
    }
}
