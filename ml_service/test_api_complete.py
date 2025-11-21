"""
Script completo para probar los 3 modelos de ML del sistema de microlearning
Autor: Sistema de Testing
Fecha: 2025
"""

import requests
import json
from typing import Dict, Any

# Configuración
API_URL = "http://localhost:5000"

# Colores para output
class Colors:
    GREEN = '\033[92m'
    RED = '\033[91m'
    YELLOW = '\033[93m'
    BLUE = '\033[94m'
    END = '\033[0m'

def print_header(text: str):
    """Imprime un encabezado formateado"""
    print(f"\n{Colors.BLUE}{'='*60}")
    print(f"  {text}")
    print(f"{'='*60}{Colors.END}\n")

def print_success(text: str):
    """Imprime mensaje de éxito"""
    print(f"{Colors.GREEN}✓ {text}{Colors.END}")

def print_error(text: str):
    """Imprime mensaje de error"""
    print(f"{Colors.RED}✗ {text}{Colors.END}")

def print_warning(text: str):
    """Imprime mensaje de advertencia"""
    print(f"{Colors.YELLOW}⚠ {text}{Colors.END}")

# Datos de prueba - Estudiante tipo 1: Nivel Básico
estudiante_basico = {
    "ciclo": 2,
    "tiempo_estudio": 8,
    "sesiones_semana": 2,
    "modulos_completados": 3,
    "evaluaciones_aprobadas": 1,
    "promedio_anterior": 10.5,
    "materia_num": 1,
    "eficiencia": 0.6,
    "productividad": 0.5
}

# Datos de prueba - Estudiante tipo 2: Nivel Intermedio
estudiante_intermedio = {
    "ciclo": 5,
    "tiempo_estudio": 15,
    "sesiones_semana": 4,
    "modulos_completados": 10,
    "evaluaciones_aprobadas": 7,
    "promedio_anterior": 14.0,
    "materia_num": 2,
    "eficiencia": 1.0,
    "productividad": 0.9
}

# Datos de prueba - Estudiante tipo 3: Nivel Avanzado
estudiante_avanzado = {
    "ciclo": 8,
    "tiempo_estudio": 25,
    "sesiones_semana": 5,
    "modulos_completados": 18,
    "evaluaciones_aprobadas": 14,
    "promedio_anterior": 17.5,
    "materia_num": 3,
    "eficiencia": 1.5,
    "productividad": 1.3
}

def test_health_endpoint():
    """Prueba el endpoint de salud"""
    print_header("TEST 1: Health Check")
    try:
        response = requests.get(f"{API_URL}/health", timeout=5)
        if response.status_code == 200:
            data = response.json()
            print_success(f"API funcionando correctamente")
            print(f"  Status: {data.get('status')}")
            print(f"  Modelos cargados: {data.get('modelos')}")
            return True
        else:
            print_error(f"Error en health check: {response.status_code}")
            return False
    except requests.exceptions.ConnectionError:
        print_error("No se pudo conectar a la API. ¿Está corriendo en http://localhost:5000?")
        return False
    except Exception as e:
        print_error(f"Error inesperado: {str(e)}")
        return False

def test_diagnostico(estudiante_data: Dict[str, Any], nombre: str):
    """Prueba el modelo de diagnóstico"""
    print_header(f"TEST 2: Modelo de Diagnóstico - {nombre}")
    try:
        response = requests.post(
            f"{API_URL}/predict/diagnostico",
            json=estudiante_data,
            timeout=5
        )
        
        if response.status_code == 200:
            data = response.json()
            print_success("Predicción exitosa")
            print(f"\n  📊 Resultados:")
            print(f"    • Nivel detectado: {data.get('nivel').upper()}")
            print(f"    • Baja retención: {'Sí' if data.get('tiene_baja_retencion') else 'No'}")
            print(f"    • Probabilidad avanzado: {data.get('probabilidad')*100:.1f}%")
            
            if data.get('temas_problematicos'):
                print(f"\n  ⚠️  Temas problemáticos:")
                for tema in data.get('temas_problematicos'):
                    print(f"    • {tema}")
            
            if data.get('contenido_recomendado'):
                print(f"\n  📚 Contenido recomendado:")
                for contenido in data.get('contenido_recomendado'):
                    print(f"    • {contenido}")
            
            return True
        else:
            print_error(f"Error {response.status_code}: {response.text}")
            return False
            
    except Exception as e:
        print_error(f"Error en diagnóstico: {str(e)}")
        return False

def test_ruta(estudiante_data: Dict[str, Any], nombre: str):
    """Prueba el modelo de rutas personalizadas"""
    print_header(f"TEST 3: Modelo de Rutas - {nombre}")
    try:
        response = requests.post(
            f"{API_URL}/predict/ruta",
            json=estudiante_data,
            timeout=5
        )
        
        if response.status_code == 200:
            data = response.json()
            print_success("Ruta generada exitosamente")
            print(f"\n  🗺️  Información de Ruta:")
            print(f"    • Tipo de ruta: {data.get('tipo_ruta')}")
            print(f"    • Progreso esperado: {data.get('progreso_esperado').upper()}")
            print(f"    • Dificultad: {data.get('dificultad_recomendada').upper()}")
            
            print(f"\n  📋 Ruta de aprendizaje:")
            for paso in data.get('ruta_aprendizaje', []):
                print(f"    {paso['paso']}. {paso['contenido']} ({paso['tipo']})")
            
            return True
        else:
            print_error(f"Error {response.status_code}: {response.text}")
            return False
            
    except Exception as e:
        print_error(f"Error en ruta: {str(e)}")
        return False

def test_riesgo(estudiante_data: Dict[str, Any], nombre: str):
    """Prueba el modelo de riesgo académico"""
    print_header(f"TEST 4: Modelo de Riesgo - {nombre}")
    try:
        response = requests.post(
            f"{API_URL}/predict/riesgo",
            json=estudiante_data,
            timeout=5
        )
        
        if response.status_code == 200:
            data = response.json()
            print_success("Evaluación de riesgo completada")
            
            nivel = data.get('nivel_riesgo', '').upper()
            color = Colors.RED if nivel == 'ALTO' else Colors.YELLOW if nivel == 'MEDIO' else Colors.GREEN
            
            print(f"\n  🎯 Evaluación de Riesgo:")
            print(f"    • Nivel de riesgo: {color}{nivel}{Colors.END}")
            print(f"    • Tiene riesgo: {'Sí' if data.get('tiene_riesgo') else 'No'}")
            print(f"    • Probabilidad: {data.get('probabilidad_riesgo')*100:.1f}%")
            print(f"    • Severidad: {data.get('severidad').upper()}")
            
            if data.get('actividades_refuerzo'):
                print(f"\n  💡 Actividades de refuerzo recomendadas:")
                for actividad in data.get('actividades_refuerzo'):
                    print(f"    • {actividad}")
            else:
                print(f"\n  ✨ No se requieren actividades de refuerzo")
            
            return True
        else:
            print_error(f"Error {response.status_code}: {response.text}")
            return False
            
    except Exception as e:
        print_error(f"Error en riesgo: {str(e)}")
        return False

def test_complete_flow(estudiante_data: Dict[str, Any], nombre: str):
    """Prueba el flujo completo con un estudiante"""
    print_header(f"FLUJO COMPLETO - Perfil: {nombre}")
    
    print("📋 Datos del estudiante:")
    print(f"  • Ciclo: {estudiante_data['ciclo']}")
    print(f"  • Tiempo de estudio: {estudiante_data['tiempo_estudio']} hrs/semana")
    print(f"  • Sesiones por semana: {estudiante_data['sesiones_semana']}")
    print(f"  • Módulos completados: {estudiante_data['modulos_completados']}")
    print(f"  • Evaluaciones aprobadas: {estudiante_data['evaluaciones_aprobadas']}")
    print(f"  • Promedio anterior: {estudiante_data['promedio_anterior']}")
    print(f"  • Eficiencia: {estudiante_data['eficiencia']}")
    print(f"  • Productividad: {estudiante_data['productividad']}")
    
    resultados = {
        'diagnostico': test_diagnostico(estudiante_data, nombre),
        'ruta': test_ruta(estudiante_data, nombre),
        'riesgo': test_riesgo(estudiante_data, nombre)
    }
    
    return all(resultados.values())

def run_all_tests():
    """Ejecuta todas las pruebas"""
    print(f"\n{Colors.BLUE}")
    print("╔════════════════════════════════════════════════════════════╗")
    print("║                                                            ║")
    print("║       SISTEMA DE TESTING - MODELOS DE MICROLEARNING       ║")
    print("║                                                            ║")
    print("╚════════════════════════════════════════════════════════════╝")
    print(f"{Colors.END}")
    
    # Test 1: Health check
    if not test_health_endpoint():
        print_error("\n❌ La API no está disponible. Asegúrate de ejecutar:")
        print("   python api_service.py")
        return
    
    # Tests con diferentes perfiles
    print_header("INICIANDO PRUEBAS CON DIFERENTES PERFILES")
    
    tests_passed = []
    
    # Perfil 1: Estudiante Básico
    tests_passed.append(test_complete_flow(estudiante_basico, "Estudiante Básico"))
    
    # Perfil 2: Estudiante Intermedio
    tests_passed.append(test_complete_flow(estudiante_intermedio, "Estudiante Intermedio"))
    
    # Perfil 3: Estudiante Avanzado
    tests_passed.append(test_complete_flow(estudiante_avanzado, "Estudiante Avanzado"))
    
    # Resumen final
    print_header("RESUMEN DE PRUEBAS")
    total = len(tests_passed)
    exitosas = sum(tests_passed)
    
    if exitosas == total:
        print_success(f"Todas las pruebas pasaron exitosamente! ({exitosas}/{total})")
        print(f"\n{Colors.GREEN}✨ ¡Los 3 modelos están funcionando correctamente! ✨{Colors.END}\n")
    else:
        print_warning(f"Algunas pruebas fallaron: {exitosas}/{total} exitosas")
        print(f"\n{Colors.YELLOW}⚠️  Revisa los errores arriba para más detalles{Colors.END}\n")

if __name__ == "__main__":
    try:
        run_all_tests()
    except KeyboardInterrupt:
        print(f"\n\n{Colors.YELLOW}⚠️  Pruebas interrumpidas por el usuario{Colors.END}\n")
    except Exception as e:
        print_error(f"\n❌ Error general: {str(e)}\n")