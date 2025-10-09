from flask import Flask, request, jsonify
from flask_cors import CORS
import joblib
import numpy as np

app = Flask(__name__)
CORS(app)

# Cargar los 3 modelos
print("Cargando modelos...")
modelo_diagnostico = joblib.load('models/diagnostico_model.pkl')
modelo_rutas = joblib.load('models/rutas_model.pkl')
modelo_riesgo = joblib.load('models/riesgo_model.pkl')
print("Modelos cargados exitosamente")

# MODELO 1: Diagnóstico del nivel del estudiante
@app.route('/predict/diagnostico', methods=['POST'])
def predict_diagnostico():
    try:
        data = request.get_json()
        
        features = np.array([[
            data['ciclo'],
            data['tiempo_estudio'],
            data['sesiones_semana'],
            data['modulos_completados'],
            data['evaluaciones_aprobadas'],
            data['promedio_anterior'],
            data['materia_num'],
            data['eficiencia'],
            data['productividad']
        ]])
        
        # El modelo ahora devuelve: 'basico', 'intermedio', o 'avanzado'
        prediction = modelo_diagnostico.predict(features)[0]
        
        # Determinar temas problemáticos según el nivel
        temas_problematicos = []
        contenido = []
        
        if prediction == 'basico':
            if data['evaluaciones_aprobadas'] < 3:
                temas_problematicos.append('Evaluaciones')
                contenido.extend(['Videos de repaso', 'Ejercicios guiados'])
            if data['eficiencia'] < 0.7:
                temas_problematicos.append('Eficiencia de estudio')
                contenido.extend(['Técnicas de estudio', 'Planificación'])
            if data['productividad'] < 0.7:
                temas_problematicos.append('Productividad')
                contenido.extend(['Gestión del tiempo', 'Práctica adicional'])
        
        # Calcular probabilidad aproximada basada en características
        prob_avanzado = 0.0
        if data['promedio_anterior'] >= 16 and data['eficiencia'] >= 1.2:
            prob_avanzado = 0.85
        elif data['promedio_anterior'] >= 13 and data['eficiencia'] >= 0.9:
            prob_avanzado = 0.55
        else:
            prob_avanzado = 0.25
        
        return jsonify({
            'nivel': prediction,
            'tiene_baja_retencion': 1 if prediction == 'basico' else 0,
            'probabilidad': float(prob_avanzado),
            'temas_problematicos': temas_problematicos,
            'contenido_recomendado': list(set(contenido))  # Eliminar duplicados
        })
    
    except Exception as e:
        return jsonify({'error': str(e)}), 400

# MODELO 2: Rutas personalizadas
@app.route('/predict/ruta', methods=['POST'])
def predict_ruta():
    try:
        data = request.get_json()
        
        features = np.array([[
            data['ciclo'],
            data['tiempo_estudio'],
            data['sesiones_semana'],
            data['modulos_completados'],
            data['evaluaciones_aprobadas'],
            data['promedio_anterior'],
            data['materia_num'],
            data['eficiencia'],
            data['productividad']
        ]])
        
        # El modelo ahora devuelve: 'refuerzo_basico', 'practica_intensiva', o 'avance_normal'
        prediction = modelo_rutas.predict(features)[0]
        
        # Generar ruta según la predicción
        if prediction == 'refuerzo_basico':
            dificultad = 'basico'
            ruta = [
                {'paso': 1, 'contenido': 'Fundamentos', 'tipo': 'video'},
                {'paso': 2, 'contenido': 'Ejercicios básicos', 'tipo': 'quiz'},
                {'paso': 3, 'contenido': 'Refuerzo', 'tipo': 'lectura'}
            ]
        elif prediction == 'practica_intensiva':
            dificultad = 'intermedio'
            ruta = [
                {'paso': 1, 'contenido': 'Repaso de conceptos', 'tipo': 'video'},
                {'paso': 2, 'contenido': 'Práctica dirigida', 'tipo': 'ejercicios'},
                {'paso': 3, 'contenido': 'Evaluación formativa', 'tipo': 'quiz'}
            ]
        else:  # avance_normal
            dificultad = 'avanzado'
            ruta = [
                {'paso': 1, 'contenido': 'Conceptos avanzados', 'tipo': 'video'},
                {'paso': 2, 'contenido': 'Caso práctico', 'tipo': 'proyecto'},
                {'paso': 3, 'contenido': 'Evaluación', 'tipo': 'quiz'}
            ]
        
        return jsonify({
            'tipo_ruta': prediction,
            'progreso_esperado': 'bajo' if prediction == 'refuerzo_basico' else 'alto',
            'dificultad_recomendada': dificultad,
            'ruta_aprendizaje': ruta
        })
    
    except Exception as e:
        return jsonify({'error': str(e)}), 400

# MODELO 3: Riesgo académico
@app.route('/predict/riesgo', methods=['POST'])
def predict_riesgo():
    try:
        data = request.get_json()
        
        # Asegurar que todos los valores sean numéricos
        features = np.array([[
            float(data['ciclo']),
            float(data['tiempo_estudio']),
            float(data['sesiones_semana']),
            float(data['modulos_completados']),
            float(data['evaluaciones_aprobadas']),
            float(data['promedio_anterior']),
            float(data['materia_num']),
            float(data['eficiencia']),
            float(data['productividad'])
        ]])
        
        # El modelo ahora devuelve: 'bajo', 'medio', o 'alto'
        prediction = modelo_riesgo.predict(features)[0]
        
        # Mapear nivel de riesgo a valores numéricos
        riesgo_numerico = {'bajo': 0, 'medio': 1, 'alto': 2}
        tiene_riesgo = 1 if prediction in ['medio', 'alto'] else 0
        
        # Calcular probabilidad aproximada
        if prediction == 'alto':
            prob_riesgo = 0.8
        elif prediction == 'medio':
            prob_riesgo = 0.5
        else:
            prob_riesgo = 0.2
        
        # Actividades de refuerzo según el nivel de riesgo
        refuerzo = []
        if prediction in ['medio', 'alto']:
            sesiones = float(data['sesiones_semana'])
            promedio = float(data['promedio_anterior'])
            eficiencia = float(data['eficiencia'])
            modulos = float(data['modulos_completados'])
            
            if sesiones < 3:
                refuerzo.append('Aumentar frecuencia de sesiones')
            if promedio < 12:
                refuerzo.append('Revisar materiales básicos')
            if eficiencia < 0.7:
                refuerzo.append('Mejorar técnicas de estudio')
            if modulos < 5:
                refuerzo.append('Completar módulos pendientes')
        
        return jsonify({
            'nivel_riesgo': prediction,
            'tiene_riesgo': tiene_riesgo,
            'probabilidad_riesgo': float(prob_riesgo),
            'severidad': prediction,
            'actividades_refuerzo': refuerzo
        })
    
    except Exception as e:
        return jsonify({'error': str(e)}), 400

@app.route('/health', methods=['GET'])
def health():
    return jsonify({'status': 'ok', 'modelos': 3})

if __name__ == '__main__':
    print("API de ML iniciada en http://localhost:5000")
    app.run(host='0.0.0.0', port=5000, debug=True)