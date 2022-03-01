<?php

namespace Modules\AEGIS\View\CardView;

class Document
{
    public function title($result)
    {
        return $result->name;
    }
}
