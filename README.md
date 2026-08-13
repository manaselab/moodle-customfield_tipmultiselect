# Campo personalizado multiselección (`customfield_tipmultiselect`)

Tipo de campo personalizado multiselección cuyas opciones proceden del
catálogo compartido del plugin `local_tipmultiselect`. Pensado para campos de
curso, de modo que un campo de curso y un campo de perfil de usuario ofrezcan
exactamente las mismas opciones. No modifica el núcleo de Moodle ni el tema.

## Qué hace

- Añade el tipo de campo personalizado **Multiselección (opciones
  compartidas)**, disponible al crear campos de curso (y en cualquier otra
  área que use campos personalizados).
- Al configurar el campo, el administrador elige la **categoría de opciones**
  (gestionada en el plugin `local_tipmultiselect`) que ofrecerá el campo.
- Quien edita el curso selecciona una o varias opciones mediante un buscador
  con autocompletado.
- Los nombres de las opciones se muestran en el idioma de cada usuario si
  usan la sintaxis del filtro multilenguaje.

## Cómo funciona

- El campo guarda los identificadores de las opciones seleccionadas; los
  nombres viven solo en el catálogo compartido.
- Si una opción se oculta en el catálogo, los cursos que ya la tenían
  seleccionada la conservan y se sigue mostrando; solo deja de ofrecerse
  para nuevas selecciones.
- La categoría de opciones no se puede cambiar una vez que el campo tiene
  datos guardados, para no invalidar las selecciones existentes.
- Al restaurar una copia de seguridad en otro sitio, los identificadores de
  opción no son válidos (limitación conocida): el catálogo es propio de cada
  sitio.

## Requisitos

- Moodle 4.5 o superior.
- Plugin `local_tipmultiselect` instalado (dependencia obligatoria).

## Instalación

1. Copiar el código en `customfield/field/tipmultiselect/` (o
   `public/customfield/field/tipmultiselect/` en sitios Moodle 5.x con
   layout `public/`).
2. Completar la instalación desde **Administración del sitio › Notificaciones**
   (o por línea de comandos: `php admin/cli/upgrade.php --non-interactive`).
3. Purgar las cachés.

## Ajustes

Este plugin no tiene ajustes de administración propios. La configuración se
hace por campo: **Administración del sitio › Cursos › Campos personalizados
de curso › Añadir un nuevo campo personalizado › Multiselección (opciones
compartidas)**, eligiendo la categoría de opciones.

## Desinstalación

El plugin se desinstala desde **Administración del sitio › Extensiones**.
Moodle elimina los campos de este tipo y sus valores asociados.

## Desarrollo

- Tests: `vendor/bin/phpunit --testsuite customfield_tipmultiselect_testsuite`.
- El valor se almacena como CSV de identificadores envuelto en comas
  (p. ej. `,3,7,12,`) en `customfield_data.charvalue` (límite 1333
  caracteres, validado al guardar).

## Licencia

GNU GPL v3 or later — 2026 3iPunt (contacte@tresipunt.com)
