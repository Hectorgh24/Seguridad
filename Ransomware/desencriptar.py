from cryptography.fernet import Fernet
import os

# Cargar la clave de encriptación previamente guardada
def load_key():
    return open("filekey.key", "rb").read()

# Desencriptar los archivos de la carpeta de prueba
def decrypt_files(key):
    fernet = Fernet(key)
    directory = "carpeta_de_prueba"

    for file_name in os.listdir(directory):
        file_path = os.path.join(directory, file_name)

        if os.path.isfile(file_path):
            with open(file_path, "rb") as file:
                encrypted_data = file.read()

            try:
                decrypted_data = fernet.decrypt(encrypted_data)

                with open(file_path, "wb") as file:
                    file.write(decrypted_data)

                print(f"[OK] Archivo restaurado: {file_name}")

            except Exception as e:
                print(f"[ERROR] No se pudo descifrar: {file_name} ({e})")

# Ejecución de la desencriptación
key = load_key()
decrypt_files(key)

print("\n==============================================")
print(" Archivos restaurados usando filekey.key")
print("==============================================\n")
