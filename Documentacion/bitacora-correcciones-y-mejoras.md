# Bitácora de correcciones y mejoras – MyPetts

## Fecha
20 de agosto de 2026

## Objetivo
Documentar la limpieza, correcciones y mejoras aplicadas al proyecto para dejarlo más estable, mantenible y consistente visualmente.

---

## 1. Estado inicial observado

Antes de iniciar la limpieza, el proyecto tenía una base sólida en arquitectura MVC, pero presentaba varios problemas de mantenimiento y consistencia:

- Configuración de base de datos hardcodeada en Database/Conexion.php
- Repetición de lógica para arrancar sesiones en varios controladores
- Duplicación de métodos y definiciones que podían generar conflictos de herencia
- Vistas con CSS inline repetido en varios archivos
- Pantallas con estructura visual no uniforme
- Algunos módulos con estilos locales que estaban duplicando la misma lógica del diseño general

Esto hacía que el proyecto se viera funcional, pero no tan ordenado ni preparado para crecimiento.

---

## 2. Correcciones de bugs detectados

### Bug 1: sesión duplicada y conflicto de método

Se detectó que en AuthController se estaban invocando métodos que intentaban iniciar sesión de forma redundante. Además, se había intentado crear un método con el mismo nombre que el método heredado de Controller, provocando errores de compatibilidad.

#### Archivo afectado
- Controllers/AuthController.php
- Controllers/Controller.php

#### Corrección aplicada
- Se dejó la lógica centralizada en Controller.php para iniciar sesión solo una vez.
- Se eliminó la sobreescritura local de ese método dentro del AuthController.
- Se usó el método heredado sin conflictos de firma.

#### Resultado
El editor dejó de reportar errores de tipo:
- Undefined method 'iniciarSesion'
- Method 'AuthController::iniciarSesion()' is not compatible with method 'Controller::iniciarSesion()'

---

### Bug 2: configuración de base de datos rígida

La conexión a MySQL estaba escrita directamente con valores fijos, lo que dificultaba despliegue, pruebas y mantenimiento.

#### Archivo afectado
- Database/Conexion.php

#### Corrección aplicada
- Se creó la configuración centralizada en Database/config.php
- La conexión ahora toma valores desde variables de entorno con fallback seguro
- Se mejoró la configuración del PDO con atributos de conexión más consistentes

#### Resultado
La conexión quedó más portable y profesional, sin depender de datos estáticos dentro del código fuente.

---

### Bug 3: repeticiones innecesarias en controladores

Se detectó que había lógica repetida y carga redundante en varios controladores y en el dashboard.

#### Archivos afectados
- Controllers/DashboardController.php
- Controllers/AuthController.php
- Controllers/Controller.php

#### Corrección aplicada
- Se redujo la repetición de bloques de sesión y validación
- Se centralizó la validación de contraseña en un método reutilizable en AuthController
- Se eliminó la carga redundante de modelos que no eran necesarios

#### Resultado
El código quedó más limpio y fácil de mantener.

---

### Bug 4: estilos duplicados en vistas

Vistas como mascotas/crear y citas/reagendar tenían CSS inline repetido, y además algunos elementos visuales estaban definidos varias veces con estilos casi idénticos.

#### Archivos afectados
- Views/mascotas/crear.php
- Views/citas/reagendar.php
- Public/css/style.css

#### Corrección aplicada
- Se movieron estilos reutilizables al CSS global
- Se eliminaron bloque de estilos repetidos dentro de las vistas
- Se dejó una base visual más uniforme para formularios, botones y cards

#### Resultado
La app se ve más coherente y el mantenimiento del estilo es mucho más sencillo.

---

## 3. Mejoras de limpieza aplicada

### 3.1 Base de sesión centralizada

Se dejó una única lógica para iniciar sesión en el controlador base, evitando que cada acción repita la misma comprobación.

#### Archivo
- Controllers/Controller.php

#### Beneficio
- Menos duplicidad
- Mejor mantenibilidad
- Menos riesgo de errores por inconsistencias

---

### 3.2 Validación reutilizable de contraseñas

Se creó una validación reutilizable para asegurar que la contraseña cumpla con la regla de mínimo 8 caracteres y al menos una letra y un número.

#### Archivo
- Controllers/AuthController.php

#### Beneficio
- Evita repetir la misma validación en varios métodos
- Hace más fácil futuras correcciones o cambios en la política de contraseñas

---

### 3.3 Estructura visual común

Se unificó parte del estilo de formularios y tablas para que toda la app tenga una apariencia más ordenada y profesional.

#### Archivo
- Public/css/style.css

#### Beneficio
- Menos código duplicado
- Mejor percepción visual
- Menor riesgo de inconsistencias entre pantallas

---

### 3.4 Formularios más consistentes

Se ajustaron vistas clave para que usaran el mismo sistema de layout y estilos de la app general.

#### Archivos
- Views/mascotas/crear.php
- Views/citas/reagendar.php

#### Beneficio
- Mejor presentación
- Menor ruido visual
- Más cohesión entre módulos

---

## 4. Verificación realizada

Se ejecutó validación de sintaxis para los archivos principales que fueron modificados.

### Comandos usados
- php -l Database/Conexion.php
- php -l Database/config.php
- php -l Controllers/Controller.php
- php -l Controllers/AuthController.php
- php -l Controllers/DashboardController.php
- php -l Public/index.php
- php -l Public/css/style.css
- php -l Views/mascotas/crear.php
- php -l Views/citas/reagendar.php

### Resultado verificado
Todos los archivos revisados devolvieron:
- No syntax errors detected

Esto confirma que la limpieza aplicada no dejó errores de sintaxis en la parte modificada del proyecto.

---

## 5. Estado actual del proyecto tras la limpieza

El proyecto quedó en mejor estado en tres sentidos principales:

1. Más estable: menos errores de compatibilidad y menos duplicación de lógica
2. Más mantenible: una configuración centralizada y validaciones reutilizables
3. Más profesional: menos CSS disperso y una interfaz más consistente

---

## 6. Pendientes recomendados

Aunque el proyecto ya está más ordenado, todavía quedan tareas importantes para avanzar:

- Revisar vistas de usuarios y vacunas para uniformarlas con el mismo estilo base
- Completar flujo de notificaciones
- Revisar validaciones y permisos por roles en todos los módulos
- Probar el flujo completo de citas y alertas
- Continuar con historial clínico, vacunas y reportes

---

## 7. Conclusión

La limpieza realizada fue necesaria y útil. No fue solo estética: además corrigió errores funcionales reales, mejoró la estructura del proyecto y dejó una base más sólida para continuar con el desarrollo de MyPetts.
