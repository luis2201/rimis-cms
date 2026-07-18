# Roles y permisos

- `USUARIO`: cuenta, perfil y postulación propia. Sin aportes, revisión ni administración.
- `INVESTIGADOR`: perfil/privacidad, aportes y publicaciones propias. Sin revisar, aprobar o publicar.
- `WEBMASTER`: gestión editorial, contenidos, postulaciones autorizadas y visibilidad de perfiles. Sin administración técnica innecesaria de usuarios/roles.
- `ADMINISTRADOR`: acceso administrativo completo.

La interfaz no constituye autorización. Rutas, middleware, Policies y servicios validan permisos, propiedad, estado y prevención de autorrevisión. Ejecute siempre `RolesAndPermissionsSeeder` después de desplegar cambios de permisos.
