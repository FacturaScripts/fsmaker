# Formularios de edición de facturas, albaranes, etc

> **ID:** 1366 | **Permalink:** formularios-de-edicion-de-facturas-albaranes-etc | **Última modificación:** 18-03-2026
> **URL oficial:** https://facturascripts.com/formularios-de-edicion-de-facturas-albaranes-etc

Los formularios de edición de facturas, albaranes, pedidos y presupuestos son respectivamente `PurchaseController` o `SalesController`, en función de si son de compras o ventas:

- Los formularios de compras heredan de la clase `PurchaseController`.
- Los formularios de venta heredan de la clase `SalesController`.

Para añadir campos o modificarlos en estas clases, se implementarán del mismo modo ya sean documentos de venta o compra. En este ejemplo elegiremos documentos de venta.

## Añadir columnas a la cabecera o pie
Para añadir una columna a la cabecer o pié del formuario debemos crear una clase que implemente uno de estos dos contratos, ya sea para documentos de venta o de compras:

- `FacturaScripts\Core\Contract\SalesModInterface`
- `FacturaScripts\Core\Contract\PurchasesModInterface`

Por convención se llamará `SalesHeaderHTMLMod` o `PurchasesHeaderHTMLMod` y se ubicará en el directorio `Mod` del plugin.

Para añadir una columna a la cabecera, registraremos un nuevo campo e implementaremos el html de ese input:

```php
public function newFields(): array
{
    return ['pruebaNewFields'];
}

public function renderField(SalesDocument $model, string $field): ?string
{
    if ($field == 'pruebaNewFields') {
        return static::pruebaNewFields($model);
    }

    return null;
}

private static function pruebaNewFields(SalesDocument $model): string
{
    $html = '<div class="col-sm"><div class="form-group">';
    $html .= 'pruebaNewFields<input class="form-control" type="text" name="pruebaNewFields" placeholder="opcional" value="">';
    $html .= '</div></div>';

    return $html;
}
```

![campo añadido a cabecera](/MyFiles/2024/11/2386.png?myft=2ceb7473f8390a5e0ec7525e189a494606175b28)

### Añadir columna al modal detalles
Para añadir una columna al modal "Detalle", registraremos un nuevo campo e implementaremos el html de ese input:

```php
public function newModalFields(): array
{
    return ['pruebaNewModalFields'];
}

public function renderField(SalesDocument $model, string $field): ?string
{
    if ($field == 'pruebaNewModalFields') {
        return static::pruebaNewModalFields($model);
    }

    return null;
}

private static function pruebaNewModalFields(SalesDocument $model): string
{
    $html = '<div class="col-sm"><div class="form-group">';
    $html .= 'pruebaNewModalFields<input class="form-control border-danger" type="text" name="pruebaNewModalFields" placeholder="opcional" value="">';
    $html .= '</div></div>';

    return $html;
}
```

![campo añadido a ventana detalles](/MyFiles/2024/11/2387.png?myft=06d9e15d5755c6b99c23b478fb6f694ef19cd58f)

### Añadir botones
Para añadir 'botones' o 'input select' a la cabecera, igualmente debemos registrar el botón como nuevo campo e implementar el html:

```php
public function newBtnFields(): array
{
    return ['pruebaNewBtnFields'];
}

public function renderField(SalesDocument $model, string $field): ?string
{
    if ($field == 'pruebaNewBtnFields') {
        return static::pruebaNewBtnFields($model);
    }

    return null;
}

private static function pruebaNewBtnFields(SalesDocument $model): string
{
    $html = '<div class="col-sm-auto"><div class="form-group">';
    $html .= '<button type="button" class="btn btn-danger">pruebaNewBtnFields</button>';
    $html .= '</div></div>';

    return $html;
}
```

![añadir botón cabecera](/MyFiles/2024/11/2388.png?myft=f5b11295a92a66c4707a7c28857433cf6a4954db)

En los metodos `newFields()`, `newModalFields()`, `newBtnFields()` se podrá retornar varios campos a renderizar, por ejemplo:

```php
public function newFields(): array
{
    return ['campo1', 'campo2'];
}
```

En los métodos `apply()` y `applyBefore()`, obtendriamos los datos de los nuevos campos que hemos añadido para poder guardarlos en nuestro modelo.

```
public function apply(SalesDocument &$model, array $formData): void
{
	$model->pruebaNewFields = $formData['pruebaNewFields'];
	$model->pruebaNewModalFields = $formData['pruebaNewModalFields'];
}
```

El código completo de esta clase de ejemplo sería el siguiente:

```php
class SalesHeaderHTMLMod implements SalesModInterface
{

    public function apply(SalesDocument &$model, array $formData): void
    {
        $model->pruebaNewFields = $formData['pruebaNewFields'];
				$model->pruebaNewModalFields = $formData['pruebaNewModalFields'];
    }

    public function applyBefore(SalesDocument &$model, array $formData): void
    {
        // TODO: Implement applyBefore() method.
    }

    public function assets(): void
    {
        // TODO: Implement assets() method.
    }

    public function newFields(): array
    {
        return ['pruebaNewFields'];
    }

    public function newModalFields(): array
    {
        return ['pruebaNewModalFields'];
    }

    public function newBtnFields(): array
    {
        return ['pruebaNewBtnFields'];
    }

    public function renderField(SalesDocument $model, string $field): ?string
    {
        if ($field == 'pruebaNewFields') {
            return static::pruebaNewFields($model);
        }

        if ($field == 'pruebaNewModalFields') {
            return static::pruebaNewModalFields($model);
        }

        if ($field == 'pruebaNewBtnFields') {
            return static::pruebaNewBtnFields($model);
        }

        return null;
    }

    private static function pruebaNewFields(SalesDocument $model): string
    {
        $html = '<div class="col-sm"><div class="form-group">';
        $html .= 'pruebaNewFields<input class="form-control border-danger" type="text" name="pruebaNewFields" placeholder="opcional" value="">';
        $html .= '</div></div>';

        return $html;
    }

    private static function pruebaNewModalFields(SalesDocument $model): string
    {
        $html = '<div class="col-sm"><div class="form-group">';
        $html .= 'pruebaNewModalFields<input class="form-control border-danger" type="text" name="pruebaNewModalFields" placeholder="opcional" value="">';
        $html .= '</div></div>';

        return $html;
    }

    private static function pruebaNewBtnFields(SalesDocument $model): string
    {
        $html = '<div class="col-sm-auto"><div class="form-group">';
        $html .= '<button type="button" class="btn btn-danger">pruebaNewBtnFields</button>';
        $html .= '</div></div>';

        return $html;
    }
}
```


## Añadir columnas a las líneas

Para añadir o modificar columnas en las líneas de los formularios debemos crear una clase que implemente uno de estos contratos, ya sea para documentos de venta o de compras:

- `FacturaScripts\Core\Contract\SalesLineModInterface`
- `FacturaScripts\Core\Contract\PurchasesLineModInterface`

Por convención se llamará `SalesLineHTMLMod` o `PurchasesLineHTMLMod` y se ubicará en el directorio `Mod` del plugin.

Para añadir una columna a la línea, registraremos un nuevo campo e implementaremos el html de ese input:

```php
public function newFields(): array
{
    return ['pruebaNewFields'];
}

public function newTitles(): array
{
	return ['pruebaNewFields'];
}

public function renderField(string $idlinea, SalesDocumentLine $line, SalesDocument $model, string $field): ?string
{
    if ($field == 'pruebaNewFields') {
        return static::pruebaNewFields($idlinea, $line, $model);
    }

    return null;
}

public function renderTitle(SalesDocument $model, string $field): ?string
{
    if ($field == 'pruebaNewFields') {
        return static::pruebaNewFieldsTitle();
    }

    return null;
}

private static function pruebaNewFields(string $idlinea, SalesDocumentLine $line, SalesDocument $model): string
{
    $html = '<div class="col-sm col-lg-1 order-2">';
		$html .= '<div class="d-lg-none mt-3 small">Prueba</div>';
    $html .= '<input class="form-control" type="text" name="pruebaNewFields_' . $idlinea . '" value="' . $line->pruebaNewFields . '">';
    $html .= '</div>';

    return $html;
}

private function pruebaNewFieldsTitle(): string
{
        return '<div class="col-lg-1 order-2">Prueba</div>';
}
```

**Nota**: Podemos decidir en que orden aparece la columna con la clase `order-1`, `order-2`, `order-3`, hasta llegar a `order-12`. Debemos colocar tanto al renderiar la columna como en el título de la columna.

![campo añadido a la linea](/MyFiles/2024/11/2389.png?myft=88deeca662eec61c73c4871220ebe6c972613d74)

### Botón 3 puntos
Para añadir una columna al modal de la línea "...", registraremos un nuevo campo e implementaremos el html de ese input:

```php
public function newModalFields(): array
{
    return ['pruebaNewModalFields'];
}

public function renderField(string $idlinea, SalesDocumentLine $line, SalesDocument $model, string $field): ?string
{
    if ($field == 'pruebaNewModalFields') {
        return static::pruebaNewModalFields($idlinea, $line, $model);
    }

    return null;
}

private static function pruebaNewModalFields(string $idlinea, SalesDocumentLine $line, SalesDocument $model): string
{
    $html = '<div class="col-6"><div class="mb-2">';
    $html .= 'pruebaNewModalFields<input class="form-control border-danger" type="text" name="pruebaNewModalFields_' . $idlinea . '" value="' . $line->pruebaNewModalFields . '">';
    $html .= '</div></div>';

    return $html;
}
```

Con la función `map()` podemos conseguir que se actualicen los datos de una columna en especial al editar la línea, sin perder el foco del cursor de la misma línea.

```
public function map(array $lines, SalesDocument $model): array
{
	$map = [];
	$num = 0;
	foreach ($lines as $line) {
		$num++;
		$idlinea = $line->idlinea ?? 'n' . $num;
		$map['pruebaNewFields_' . $idlinea] = 'aquí hacemos nuestro cálculo';
	}
	return $map;
}
```

En los métodos `apply()` y `applyToLine()`, obtendriamos los datos de los nuevos campos que hemos añadido para poder guardarlos en nuestro modelo.

```
public function applyToLine(array $formData, SalesDocumentLine &$line, string $id): void
{
	$line->alto = $formData['pruebaNewFields_' . $id];
	$line->alto = $formData['pruebaNewModalFields_' . $id];
}
```

![campo añadido al modal de la linea](/MyFiles/2024/11/2390.png?myft=a42e2d572c158cbd62778e6b99ef223bd378f894)

El código completo de esta clase de ejemplo sería el siguiente:

```php
class SalesLineMod implements SalesLineModInterface
{

	public function apply(SalesDocument &$model, array &$lines, array $formData): void
	{
	}

	public function applyToLine(array $formData, SalesDocumentLine &$line, string $id): void
	{
			$line->alto = $formData['pruebaNewFields_' . $id];
			$line->alto = $formData['pruebaNewModalFields_' . $id];
	}

	public function assets(): void
	{
			// TODO: Implement assets() method.
	}
		
	public function getFastLine(SalesDocument $model, array $formData): ?SalesDocumentLine
	{
			return null;
	}
		
	public function map(array $lines, SalesDocument $model): array
	{
			$map = [];
			$num = 0;
			foreach ($lines as $line) {
				$num++;
				$idlinea = $line->idlinea ?? 'n' . $num;
				$map['pruebaNewFields_' . $idlinea] = 'aquí hacemos nuestro cálculo';
			}
		return $map;
	}

	public function newFields(): array
	{
			return ['pruebaNewFields'];
	}

	public function newModalFields(): array
	{
			return ['pruebaNewModalFields'];
	}

	public function newTitles(): array
	{
			return ['pruebaNewFields'];
	}

	public function renderField(string $idlinea, SalesDocumentLine $line, SalesDocument $model, string $field): ?string
	{
			if ($field == 'pruebaNewFields') {
					return static::pruebaNewFields($idlinea, $line, $model);
			}
		
			if ($field == 'pruebaNewModalFields') {
					return static::pruebaNewModalFields($idlinea, $line, $model);
			}
		
			return null;
	}
		
	public function renderTitle(SalesDocument $model, string $field): ?string
	{
		if ($field == 'pruebaNewFields') {
			return static::pruebaNewFieldsTitle();
		}
		
		return null;
	}
	
	private static function pruebaNewFields(string $idlinea, SalesDocumentLine $line, SalesDocument $model): string
	{
		$html = '<div class="col-sm col-lg-1 order-2">';
		$html .= '<div class="d-lg-none mt-3 small">Prueba</div>';
		$html .= '<input class="form-control" type="text" name="pruebaNewFields_' . $idlinea . '" value="' . $line->pruebaNewFields . '">';
		$html .= '</div>';
		return $html;
	}
	
	private function pruebaNewFieldsTitle(): string
	{
		return '<div class="col-lg-1 order-2">Prueba</div>';
	}
	
	private static function pruebaNewModalFields(string $idlinea, SalesDocumentLine $line, SalesDocument $model): string
	{
		$html = '<div class="col-6"><div class="mb-2">';
		$html .= 'pruebaNewModalFields<input class="form-control border-danger" type="text" name="pruebaNewModalFields_' . $idlinea . '" value="' . $line->pruebaNewModalFields . '">';
		$html .= '</div></div>';
		return $html;
	}
}
```


## Modificar los cálculos
Para modificar los cálculos de totales y subtotales de los albaranes, factura, etc ...  debemos crear una clase que implemente el [CalculatorModInterface](https://facturascripts.com/publicaciones/como-modificar-el-calculator-desde-un-plugin).

## Archivo Init
Todos los archivos añadidos al Mod de los docuementos, ya sean de compra o venta, se deben añadir al archivo init para cargalos en la ejecución.

```
use FacturaScripts\Core\Template\InitClass;
use FacturaScripts\Core\Lib\AjaxForms\PurchasesHeaderHTML;
use FacturaScripts\Core\Lib\AjaxForms\PurchasesLineHTML;
use FacturaScripts\Core\Lib\AjaxForms\PurchasesFooterHTML;
use FacturaScripts\Core\Lib\AjaxForms\SalesHeaderHTML;
use FacturaScripts\Core\Lib\AjaxForms\SalesLineHTML;
use FacturaScripts\Core\Lib\AjaxForms\SalesFooterHTML;


class Init extends InitClass
{
    public function init()
    {
        PurchasesHeaderHTML::addMod(new Mod\PurchasesHeaderMod());
        PurchasesLineHTML::addMod(new Mod\PurchasesLineMod());
        PurchasesFooterHTML::addMod(new Mod\PurchasesFooterMod());
        SalesHeaderHTML::addMod(new Mod\SalesHeaderMod());
        SalesLineHTML::addMod(new Mod\SalesLineMod());
        SalesFooterHTML::addMod(new Mod\SalesFooterMod());
    }

    public function uninstall(): void
    {
    }

    public function update(): void
    {
    }
}
```
