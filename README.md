# Parameters manager

## Co to umí?
- Importovat výchozí hodnoty parametrů z konfigu.
- Cache parametrů z databáze
- Invalidaci cache parameterů při změně nebo v debug baru
- Import změněných parametrů
- Zobrazit aktuální a výchozí hodnoty v debug baru
- Import nových parametrů z debug baru

## Použití

Registrace v konfigu:
```yaml
extensions:
    params: WebChemistry\Parameters\DI\Extension
```

Použití:
```yaml
params:
    boolean: yes
    float: 1.0
    int: 5
    empty: # NULL
    array:
        first: first
        second: second
        array:
            third: third
```

## Presenter, latte

```php
<?php

namespace App\Presenters;

use Nette;
use WebChemistry\Parameters\Traits\TPresenter;

class BasePresenter extends Nette\Application\UI\Presenter {
    
    use TPresenter;
    
    // Použití v presenteru přes $this->parametersProvider
}
```

```html
Můj parameter: {$parameters['first']}
Můj parameter: {$parameters->first}
Můj parameter: {$parameters->array->first}
Můj parameter: {$parameters->array['first']}
```

## Úprava parametrů

```php
$parametersProvider->myVariable = 'new value';

$parametersProvider->merge();
```