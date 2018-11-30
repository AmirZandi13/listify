<?php

namespace Lookitsatravis\Listify;

class HasScopeChanged
{
    /**
     * Returns whether the scope has changed during the course of interaction with the model
     * @return boolean
     */
    public function handle ($listify)
    {
        $theScope = $listify->scopeName();

        if (is_string($theScope)) {
            if (!$listify->stringScopeValue) {
                $listify->stringScopeValue = $theScope;
                return FALSE;
            }

            return $theScope != $listify->stringScopeValue;
        }

        $reflector = new \ReflectionClass($theScope);
        if ($reflector->getName() == 'Illuminate\Database\Eloquent\Relations\BelongsTo') {
            $originalVal = $listify->getOriginal()[$theScope->getForeignKey()];
            $currentVal = $listify->getAttribute($theScope->getForeignKey());

            if ($originalVal != $currentVal) return TRUE;
        } else if ($reflector->getName() == 'Illuminate\Database\Query\Builder') {
            if (!$listify->stringScopeValue) {
                $listify->stringScopeValue = (new GetConditionStringFromQueryBuilder())->handle($theScope);
                return FALSE;
            }

            $theQuery = (new GetConditionStringFromQueryBuilder())->handle($theScope);
            if ($theQuery != $listify->stringScopeValue) return TRUE;
        }

        return FALSE;
    }
}