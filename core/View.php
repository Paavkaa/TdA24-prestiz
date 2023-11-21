<?php
namespace Core;

class View {
    protected string  $viewFile;

    public function __construct($viewFile) {
        $this->viewFile = $viewFile;
    }

    public function render($data): void
    {
        // Extrahujeme data, aby byly přímo přístupné v pohledu
        extract($data);

        // Vložíme pohled
        include($this->viewFile);
    }
}
