---
name: crear-controlador
description: Crea controladores de FacturaScripts (ListController, EditController o PanelController) con su XMLView asociado.
triggers:
  - crear controlador
  - nuevo controlador
  - crear listado
  - crear formulario edición
  - crear panel
---

# Skill: Crear Controlador FacturaScripts

Cuando el usuario pida crear un controlador, determina el tipo adecuado y genera el código.

## Tipos de controlador

| Tipo | Uso | Nombre convención |
|------|-----|-------------------|
| `ListController` | Listado de registros con filtros | `ListNombreModelo` |
| `EditController` | Formulario de edición de un registro | `EditNombreModelo` |
| `PanelController` | Combinación de vistas (cabecera + pestañas) | `EditNombreModelo` |

## ListController

Archivo: `Plugins/MiPlugin/Controller/ListNombreModelo.php`

```php
<?php

namespace FacturaScripts\Plugins\MiPlugin\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

class ListNombreModelo extends ListController
{
    public function getPageData(): array
    {
        $page = parent::getPageData();
        $page['title'] = 'nombre-modelo'; // clave de traducción
        $page['menu'] = 'sales';          // ventas, purchases, accounting, admin
        $page['icon'] = 'fas fa-list';
        return $page;
    }

    protected function createViews()
    {
        $this->createViewsNombreModelo();
    }

    protected function createViewsNombreModelo(string $viewName = 'ListNombreModelo'): void
    {
        $this->addView($viewName, 'NombreModelo', 'nombre-modelo', 'fas fa-list')
            ->addOrderBy(['name'], 'name')
            ->addOrderBy(['creation_date'], 'date', 2)
            ->addSearchFields(['name']);
    }
}
```

XMLView: `Plugins/MiPlugin/XMLView/ListNombreModelo.xml`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<view>
    <columns>
        <column name="name" order="100">
            <widget type="text" fieldname="name" />
        </column>
        <column name="creation-date" order="200">
            <widget type="date" fieldname="creation_date" />
        </column>
        <column name="active" display="center" order="300">
            <widget type="checkbox" fieldname="active" />
        </column>
    </columns>
</view>
```

## EditController

Archivo: `Plugins/MiPlugin/Controller/EditNombreModelo.php`

```php
<?php

namespace FacturaScripts\Plugins\MiPlugin\Controller;

use FacturaScripts\Core\Lib\ExtendedController\EditController;

class EditNombreModelo extends EditController
{
    public function getModelClassName(): string
    {
        return 'NombreModelo';
    }

    public function getPageData(): array
    {
        $page = parent::getPageData();
        $page['title'] = 'nombre-modelo';
        $page['menu'] = 'sales';
        $page['icon'] = 'fas fa-edit';
        $page['showonmenu'] = false; // No mostrar en el menú principal
        return $page;
    }
}
```

XMLView: `Plugins/MiPlugin/XMLView/EditNombreModelo.xml`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<view>
    <columns>
        <group name="data" numcolumns="8">
            <column name="name" numcolumns="8" order="100">
                <widget type="text" fieldname="name" required="true" />
            </column>
            <column name="creation-date" numcolumns="4" order="200">
                <widget type="date" fieldname="creation_date" />
            </column>
        </group>
        <group name="options" numcolumns="4">
            <column name="active" order="100">
                <widget type="checkbox" fieldname="active" />
            </column>
        </group>
    </columns>
</view>
```

## Menús disponibles

- `sales` — Ventas
- `purchases` — Compras
- `accounting` — Contabilidad
- `warehouse` — Almacén
- `admin` — Administrador

## Añadir filtros al ListController

```php
// En createViewsNombreModelo():
->addFilterCheckbox($viewName, 'active', 'active')
->addFilterDatePicker($viewName, 'creation_date', 'date')
->addFilterSelect($viewName, 'estado', 'state', 'estados', 'nombre')
->addFilterPeriod($viewName, 'creation_date', 'period')
```

## Documentación relacionada
- `.claude/docs/listcontroller.md`
- `.claude/docs/editcontroller.md`
- `.claude/docs/panelcontroller.md`
- `.claude/docs/las-vistas-xml-xmlview.md`
