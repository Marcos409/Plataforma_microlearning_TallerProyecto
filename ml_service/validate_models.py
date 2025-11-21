"""
Script para validar que los modelos .pkl están correctamente guardados y funcionan
"""

import joblib
import numpy as np
import os
from pathlib import Path

# Colores para output
class Colors:
    GREEN = '\033[92m'
    RED = '\033[91m'
    YELLOW = '\033[93m'
    BLUE = '\033[94m'
    CYAN = '\033[96m'
    END = '\033[0m'

def print_header(text: str):
    print(f"\n{Colors.BLUE}{'='*70}")
    print(f"  {text}")
    print(f"{'='*70}{Colors.END}\n")

def print_success(text: str):
    print(f"{Colors.GREEN}✓ {text}{Colors.END}")

def print_error(text: str):
    print(f"{Colors.RED}✗ {text}{Colors.END}")

def print_info(text: str):
    print(f"{Colors.CYAN}ℹ {text}{Colors.END}")

def check_file_exists(filepath: str) -> bool:
    """Verifica si un archivo existe"""
    return Path(filepath).exists()

def get_file_size(filepath: str) -> str:
    """Obtiene el tamaño del archivo en formato legible"""
    size_bytes = os.path.getsize(filepath)
    if size_bytes < 1024:
        return f"{size_bytes} B"
    elif size_bytes < 1024**2:
        return f"{size_bytes/1024:.2f} KB"
    else:
        return f"{size_bytes/(1024**2):.2f} MB"

def validate_model(model_path: str, model_name: str) -> bool:
    """Valida un modelo específico"""
    print_header(f"Validando: {model_name}")
    
    # 1. Verificar existencia
    if not check_file_exists(model_path):
        print_error(f"Archivo no encontrado: {model_path}")
        return False
    print_success(f"Archivo encontrado: {model_path}")
    
    # 2. Verificar tamaño
    file_size = get_file_size(model_path)
    print_info(f"Tamaño del archivo: {file_size}")
    
    # 3. Intentar cargar el modelo
    try:
        model = joblib.load(model_path)
        print_success("Modelo cargado correctamente")
    except Exception as e:
        print_error(f"Error al cargar el modelo: {str(e)}")
        return False
    
    # 4. Verificar tipo de modelo
    model_type = type(model).__name__
    print_info(f"Tipo de modelo: {model_type}")
    
    # 5. Verificar atributos básicos
    if hasattr(model, 'n_features_in_'):
        print_info(f"Número de características: {model.n_features_in_}")
    
    if hasattr(model, 'classes_'):
        print_info(f"Clases del modelo: {list(model.classes_)}")
    
    # 6. Hacer una predicción de prueba
    try:
        # Datos de prueba (9 características)
        test_data = np.array([[
            5,      # ciclo
            15.0,   # tiempo_estudio
            3,      # sesiones_semana
            8,      # modulos_completados
            6,      # evaluaciones_aprobadas
            13.5,   # promedio_anterior
            2,      # materia_num
            0.95,   # eficiencia
            0.85    # productividad
        ]])
        
        prediction = model.predict(test_data)
        print_success(f"Predicción de prueba exitosa: {prediction[0]}")
        
        # Intentar obtener probabilidades si está disponible
        if hasattr(model, 'predict_proba'):
            proba = model.predict_proba(test_data)
            print_info(f"Probabilidades disponibles: Sí")
        
    except Exception as e:
        print_error(f"Error en predicción de prueba: {str(e)}")
        return False
    
    print_success(f"✨ Modelo {model_name} validado correctamente\n")
    return True

def validate_all_models():
    """Valida todos los modelos del proyecto"""
    print(f"\n{Colors.BLUE}")
    print("╔══════════════════════════════════════════════════════════════════╗")
    print("║                                                                  ║")
    print("║          VALIDADOR DE MODELOS - SISTEMA MICROLEARNING           ║")
    print("║                                                                  ║")
    print("╚══════════════════════════════════════════════════════════════════╝")
    print(f"{Colors.END}")
    
    models_dir = "models"
    
    # Verificar que existe la carpeta models
    if not os.path.exists(models_dir):
        print_error(f"La carpeta '{models_dir}/' no existe")
        print_info("Ejecuta primero: python train_models.py")
        return
    
    print_success(f"Carpeta '{models_dir}/' encontrada\n")
    
    # Definir los modelos a validar
    models_to_validate = {
        'diagnostico_model.pkl': 'Modelo de Diagnóstico',
        'rutas_model.pkl': 'Modelo de Rutas Personalizadas',
        'riesgo_model.pkl': 'Modelo de Riesgo Académico'
    }
    
    results = {}
    
    # Validar cada modelo
    for filename, name in models_to_validate.items():
        filepath = os.path.join(models_dir, filename)
        results[name] = validate_model(filepath, name)
    
    # Resumen final
    print_header("RESUMEN DE VALIDACIÓN")
    
    total = len(results)
    valid = sum(results.values())
    
    for model_name, is_valid in results.items():
        status = f"{Colors.GREEN}✓ VÁLIDO{Colors.END}" if is_valid else f"{Colors.RED}✗ INVÁLIDO{Colors.END}"
        print(f"  {model_name}: {status}")
    
    print(f"\n{'─'*70}")
    
    if valid == total:
        print(f"{Colors.GREEN}")
        print(f"  ✨ ¡ÉXITO! Todos los modelos ({valid}/{total}) están funcionando correctamente")
        print(f"{Colors.END}")
        print(f"\n{Colors.CYAN}Siguiente paso:{Colors.END}")
        print(f"  1. Ejecuta: python api_service.py")
        print(f"  2. Luego ejecuta: python test_api_complete.py")
    else:
        print(f"{Colors.YELLOW}")
        print(f"  ⚠️  Algunos modelos tienen problemas: {valid}/{total} válidos")
        print(f"{Colors.END}")
        print(f"\n{Colors.CYAN}Solución:{Colors.END}")
        print(f"  Ejecuta: python train_models.py")
        print(f"  Esto regenerará todos los modelos\n")

def check_dependencies():
    """Verifica que las dependencias estén instaladas"""
    print_header("Verificando Dependencias")
    
    required_packages = [
        'joblib',
        'numpy',
        'sklearn'
    ]
    
    missing = []
    
    for package in required_packages:
        try:
            __import__(package)
            print_success(f"{package} instalado")
        except ImportError:
            print_error(f"{package} NO encontrado")
            missing.append(package)
    
    if missing:
        print(f"\n{Colors.YELLOW}Instala las dependencias faltantes:{Colors.END}")
        print(f"  pip install -r requirements.txt\n")
        return False
    
    print_success("Todas las dependencias están instaladas\n")
    return True

if __name__ == "__main__":
    try:
        # Primero verificar dependencias
        if not check_dependencies():
            exit(1)
        
        # Luego validar modelos
        validate_all_models()
        
    except KeyboardInterrupt:
        print(f"\n\n{Colors.YELLOW}⚠️  Validación interrumpida por el usuario{Colors.END}\n")
    except Exception as e:
        print_error(f"\n❌ Error general: {str(e)}\n")