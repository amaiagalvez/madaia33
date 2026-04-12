# Esquema de Metricas de Calidad para Votaciones

## 1. Cobertura y participacion
- Elegibles: E
- Votantes unicos: V
- Participacion (%): P = (V / E) x 100

## 2. Calidad de validacion
- Intentos totales: I
- Intentos bloqueados: B
- Tasa de bloqueo (%): TB = (B / I) x 100
- Votos validos: VV
- Tasa de validez (%): TV = (VV / V) x 100

## 3. Integridad tecnica
- Resultado sistema: Rs
- Resultado recuento independiente: Ri
- Desviacion: D = |Rs - Ri| (objetivo: D = 0)
- Incidencias criticas por votacion: IC

## 4. Eficiencia operativa
- Tiempo medio de resolucion de incidencias (MTTR).
- Tiempo de cierre administrativo tras fin de votacion.

## 5. Resultado en modelo 1 persona 1 voto
- Votos por opcion: conteo simple.
- Porcentaje por opcion: (votos_opcion / VV) x 100.

## 6. Resultado en modelo ponderado por coeficiente
- Peso total elegible: WE = suma(w_i)
- Peso emitido: WV = suma(w_i de votantes)
- Participacion ponderada (%): PP = (WV / WE) x 100
- Resultado por opcion j: Rj = suma(w_i de votos a opcion j)
- Porcentaje ponderado opcion j (%): (Rj / suma(Rj)) x 100

## 7. KPIs para dashboard trimestral
- Participacion media por comunidad.
- Tasa media de bloqueos por control.
- Incidencias criticas por 100 votaciones.
- Desviacion de recuento (objetivo cero).
- Tiempo medio de cierre de acta.

## 8. Umbrales sugeridos (semaforo)
- Verde:
    - Desviacion = 0
    - Incidencias criticas = 0
    - Cierre de acta en plazo definido
- Ambar:
    - Incidencias no criticas o retrasos moderados
- Rojo:
    - Cualquier desviacion de recuento
    - Incidencia critica sin resolver

## 9. Evidencias minimas por metrica
- Fuente de datos (tabla/reporte).
- Fecha y hora de extraccion.
- Responsable de validacion.
- Metodo de recalculo independiente.
