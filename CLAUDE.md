# Reglas de desarrollo: Barberortiz

## Arquitectura
- Stack: Laravel, TailwindCSS, Alpine.js.
- Enfoque: Gestión de agenda, disponibilidad y perfiles de cliente.
- Mantén las vistas limpias; utiliza componentes Blade reutilizables.

## Estilo de Código
- PSR-12.
- Nombres de funciones y variables en inglés/español consistente (prefiero español para la lógica de negocio).
- Prioriza legibilidad en las consultas de calendario.

## Reglas de trabajo
- Al modificar la lógica de reservas, verifica siempre los conflictos de horario.
- Documenta cualquier cambio en la validación de turnos.
- Mantén la consistencia en el diseño responsive (Tailwind).