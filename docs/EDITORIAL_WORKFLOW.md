# Flujo editorial

`draft → submitted → under_review → observed → submitted` o `under_review → approved/rejected`.

Un contenido aprobado permanece con estado de publicación `draft` hasta que un editor ejecuta la publicación. **Aprobado no significa publicado.** Un contenido publicado puede despublicarse sin perder la aprobación.

Las transiciones se ejecutan en transacciones con bloqueo, historial, actor y fecha. Fallos SMTP se registran y no revierten el cambio editorial.
