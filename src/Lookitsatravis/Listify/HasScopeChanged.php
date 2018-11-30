<?php

namespace Lookitsatravis\Listify;

use ReflectionClass;

class HasScopeChanged
{

    protected $listify;
    protected $scope;
    protected $reflector;

    public function __construct ($listify)
    {
        $this->listify = $listify;
        $this->scope = $this->listify->scopeName();
    }

    /**
     * Returns whether the scope has changed during the course of interaction with the model
     * @return boolean
     */
    public function handle()
    {
        if (is_string($this->scope)) {
            if (!$this->listify->stringScopeValue) {
                $this->listify->stringScopeValue = $this->scope;
                return FALSE;
            }
            return $this->scope != $this->listify->stringScopeValue;
        }

        $this->instanceReflection();

        return $this->nameReflector($this->reflector->getName());
    }

    protected function instanceReflection ()
    {
        $this->reflector = new ReflectionClass($this->scope);
    }

    protected function nameReflector ($reflector)
    {
        $map = [
            'Illuminate\Database\Eloquent\Relations\BelongsTo' => 'belongsTo' ,
            'Illuminate\Database\Query\Builder'                => 'builder' ,
        ];

        if (array_key_exists($reflector , $map))
        {
            $method = $map[$reflector];
            return $this->$method();
        }else{
            return FALSE;
        }

    }
    private function belongsTo()
    {
        $originalVal = $this->listify->getOriginal()[$this->scope->getForeignKey()];
        $currentVal = $this->listify->getAttribute($this->scope->getForeignKey());

        if ($originalVal != $currentVal) return TRUE;
    }

    private function builder()
    {
        if (!$this->listify->stringScopeValue) {
            $this->listify->stringScopeValue = (new GetConditionStringFromQueryBuilder())->handle($this->scope);
            return FALSE;
        }

        $theQuery = (new GetConditionStringFromQueryBuilder())->handle($this->scope);
        if ($theQuery != $this->listify->stringScopeValue) return TRUE;
    }

}