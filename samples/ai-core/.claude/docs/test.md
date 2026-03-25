# Método test() del modelo

> **ID:** 632 | **Permalink:** test-625 | **Última modificación:** 29-12-2025
> **URL oficial:** https://facturascripts.com/test-625

La función **test()** se utiliza para validar los valores de las propiedades o columnas de un modelo en FacturaScripts. **Devuelve `false`** si detecta problemas en los valores y **`true`** en caso contrario. 

Por ejemplo, devolverá **`false`** si alguna columna del modelo contiene un valor **NULL** y en la base de datos esa columna tiene una restricción **NOT NULL**. Si el modelo `Cliente` tiene el campo `cifnif` en **NULL**, la función **test()** retornará **`false`**, ya que la columna `cifnif` no puede admitir valores nulos.

## ¿Cuándo se ejecuta?

La función **test()** se ejecuta automáticamente dentro de la [función `save()` del modelo](https://facturascripts.com/publicaciones/save-782), **antes** de las funciones internas `saveInsert()` y `saveUpdate()`.

## Uso habitual

No es común llamar a esta función directamente. Lo habitual es que se personalice esta función en los modelos para realizar comprobaciones o correcciones adicionales antes de guardar los datos.

### Ejemplo de personalización:

```php
public function test(): bool
{
    // Validamos que el campo codpago solo contiene letras, números y caracteres permitidos
    if ($this->codpago && 1 !== preg_match('/^[A-Z0-9_\+\.\-\s]{1,10}$/i', $this->codpago)) {
        Tools::log()->error('invalid-alphanumeric-code', [
            '%value%' => $this->codpago,
            '%column%' => 'codpago',
            '%min%' => '1',
            '%max%' => '10'
        ]);

        return false;
    }

    return parent::test();
}
```

En este ejemplo, la función personalizada verifica que el campo `codpago` cumpla un formato alfanumérico específico. Si no lo cumple, registra el error y devuelve **`false`**.

---

**Enlaces relacionados:**
- [Los modelos](https://facturascripts.com/publication/los-modelos-228)
