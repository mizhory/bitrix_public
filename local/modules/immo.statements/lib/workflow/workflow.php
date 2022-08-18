<?php

namespace Immo\Statements\Workflow;

use CBPActivity;
use CIBlockElement;
use Immo\Statements\ModuleInterface;

class Workflow implements ModuleInterface
{
    public static function setWorkflowInstanceIdInElement(CBPActivity $context, int $elementId, string $property)
    {
        $options = [
            $property => $context->getWorkflowInstanceId()
        ];

        CIBlockElement::SetPropertyValuesEx($elementId, null, $options);
    }
}