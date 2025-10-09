import numpy as np
import pandas as pd
from sklearn.tree import DecisionTreeClassifier
from sklearn.ensemble import RandomForestClassifier
import pickle
import os

print("Generando datos de entrenamiento sintéticos...")

# Crear directorio de modelos si no existe
os.makedirs('models', exist_ok=True)

# Generar datos sintéticos para entrenamiento
np.random.seed(42)
n_samples = 500

# Características
data = {
    'ciclo': np.random.randint(1, 11, n_samples),
    'tiempo_estudio': np.random.randint(5, 40, n_samples),
    'sesiones_semana': np.random.randint(1, 7, n_samples),
    'modulos_completados': np.random.randint(0, 20, n_samples),
    'evaluaciones_aprobadas': np.random.randint(0, 15, n_samples),
    'promedio_anterior': np.random.uniform(0, 20, n_samples),
    'materia_num': np.random.randint(0, 5, n_samples),
    'eficiencia': np.random.uniform(0.5, 2.0, n_samples),
    'productividad': np.random.uniform(0.3, 1.5, n_samples)
}

df = pd.DataFrame(data)

# Generar etiquetas basadas en lógica
def generar_diagnostico(row):
    if row['promedio_anterior'] >= 16 and row['eficiencia'] >= 1.2:
        return 'avanzado'
    elif row['promedio_anterior'] >= 13 and row['eficiencia'] >= 0.9:
        return 'intermedio'
    else:
        return 'basico'

def generar_ruta(row):
    if row['modulos_completados'] < 5:
        return 'refuerzo_basico'
    elif row['evaluaciones_aprobadas'] < row['modulos_completados'] * 0.6:
        return 'practica_intensiva'
    else:
        return 'avance_normal'

def generar_riesgo(row):
    if row['promedio_anterior'] < 11 or row['sesiones_semana'] < 2:
        return 'alto'
    elif row['promedio_anterior'] < 14 or row['sesiones_semana'] < 4:
        return 'medio'
    else:
        return 'bajo'

df['diagnostico'] = df.apply(generar_diagnostico, axis=1)
df['ruta'] = df.apply(generar_ruta, axis=1)
df['riesgo'] = df.apply(generar_riesgo, axis=1)

# Características para entrenamiento
features = ['ciclo', 'tiempo_estudio', 'sesiones_semana', 'modulos_completados', 
            'evaluaciones_aprobadas', 'promedio_anterior', 'materia_num', 
            'eficiencia', 'productividad']

X = df[features]

# Entrenar modelo de diagnóstico
print("\nEntrenando modelo de diagnóstico...")
y_diagnostico = df['diagnostico']
model_diagnostico = DecisionTreeClassifier(random_state=42, max_depth=5)
model_diagnostico.fit(X, y_diagnostico)

with open('models/diagnostico_model.pkl', 'wb') as f:
    pickle.dump(model_diagnostico, f)
print("✓ Modelo de diagnóstico guardado")

# Entrenar modelo de ruta
print("\nEntrenando modelo de ruta...")
y_ruta = df['ruta']
model_ruta = RandomForestClassifier(random_state=42, n_estimators=50)
model_ruta.fit(X, y_ruta)

with open('models/rutas_model.pkl', 'wb') as f:
    pickle.dump(model_ruta, f)
print("✓ Modelo de ruta guardado")

# Entrenar modelo de riesgo
print("\nEntrenando modelo de riesgo...")
y_riesgo = df['riesgo']
model_riesgo = DecisionTreeClassifier(random_state=42, max_depth=4)
model_riesgo.fit(X, y_riesgo)

with open('models/riesgo_model.pkl', 'wb') as f:
    pickle.dump(model_riesgo, f)
print("✓ Modelo de riesgo guardado")

print("\n¡Todos los modelos han sido entrenados exitosamente!")
print(f"Datos de entrenamiento: {n_samples} muestras")
print(f"Características: {len(features)}")