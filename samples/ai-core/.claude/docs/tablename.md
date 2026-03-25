# Método tableName() del modelo

> **ID:** 638 | **Permalink:** tablename-298 | **Última modificación:** 29-12-2025
> **URL oficial:** https://facturascripts.com/tablename-298

El método `tableName()` es esencial en FacturaScripts, ya que devuelve el nombre de la tabla de la base de datos que utilizará el modelo. Este método es crucial para asegurar la correcta interacción del modelo con la base de datos, junto con el [método `primaryColumn()`](https://facturascripts.com/publicaciones/primarycolumn-492).

### Ejemplo de Implementación

```php
public static function tableName(): string
{
    // Reemplace 'mi_tabla' por el nombre real de la tabla
    return 'mi_tabla';
}
```

### Configuración Adicional

Para completar la configuración, es necesario crear un archivo XML que tenga el mismo nombre que la tabla. Este archivo debe ubicarse dentro de la carpeta `Table` del plugin y contendrá [la definición de la estructura de la tabla](https://facturascripts.com/publicaciones/la-definicion-de-la-estructura-de-la-tabla-514).

### Recursos Relacionados

- [Los modelos](https://facturascripts.com/publication/los-modelos-228)
