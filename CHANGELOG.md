
# Changelog
Todos los cambios notables se añadirán en este fichero

Formato basado en [Mantenga un Changelog](https://keepachangelog.com/en/1.0.0/),
y utiliza [Versionado semántico](https://semver.org/lang/es/).

## [Unreleased]
### Añadido
- Caché en base de datos y fichero
    - Recuperar datos
    - Insertar datos
    - Eliminar datos
    - Verificar si un dato existe en cache
    - Verificar si un dato ha expirado
    - Recuperar un dato y, posteriormente, borrarlo de la caché
    - Recuperar la lista de elementos guardados en la caché
    - Borrar todos los elementos de la memoría caché
    - Borrar aquellos elementos que tengan una fecha de creación superior a otra determinada
    - Contar los elementos existentes en la caché
- Caché de base de datos
    - Añadir etiquetas de caché
    - Buscar por etiquetas de caché

