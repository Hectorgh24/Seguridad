#!/usr/bin/env bash
# detect_keylogger.sh

# --- Definición de Patrones ---
# Define una lista de palabras clave (regex) que suelen aparecer en nombres de scripts
# maliciosos o en sus argumentos.
# Se busca "pynput" (librería usada en el ejemplo anterior), "listener", "log", etc.
PATTERNS="keylog|keylogger|pynput|keyboard|listener|keylogs|key_logs|keystroke|keystrokes|klog"

echo "=== Buscando procesos sospechosos (ps aux) ==="
# 1. ps aux: Lista TODOS los procesos del sistema con detalles.
# 2. egrep: Filtra la salida buscando intérpretes comunes (python, perl, ruby...)
#    o los patrones definidos arriba.
# 3. || true: Evita que el script se detenga si grep no encuentra nada (código de salida 1).
ps aux | egrep -i --color=always "python|python3|perl|ruby|node|mono|$PATTERNS" || true

echo
echo "=== Procesos Python (largo) con línea de comando completa ==="
# pgrep -f python: Busca PIDs de procesos que contengan "python" en su nombre o argumentos.
for pid in $(pgrep -d' ' -f python || true); do
    echo "---- PID: $pid ----"
    # Muestra detalles específicos de ese PID.
    # --cols 200: Amplía el ancho de columna para intentar ver el comando completo.
    ps -p $pid -o pid,user,uid,cmd --cols 200
done

echo
echo "=== Procesos cuyos cmdline contienen patrones clave (Análisis Profundo) ==="
# A veces 'ps' trunca la salida si el comando es muy largo. 
# Para ver la verdad absoluta, leemos directamente de la memoria del kernel en /proc.

# Iteramos sobre todos los PIDs existentes.
while IFS= read -r line; do
    pid=$(echo "$line" | awk '{print $2}')
    
    # Leemos el archivo virtual /proc/<PID>/cmdline.
    # Este archivo contiene el comando exacto tal como lo ve el kernel.
    # tr '\0' ' ': Los argumentos en cmdline están separados por bytes nulos, los convertimos a espacios.
    cmd=$(tr '\0' ' ' < /proc/$pid/cmdline 2>/dev/null)
    
    # Verificamos si este comando "crudo" contiene nuestros patrones sospechosos.
    if echo "$cmd" | egrep -qi "$PATTERNS"; then
        echo "PID $pid : $cmd"
    fi
done < <(ps -eo pid --no-headers) # Input loop: lista limpia de PIDs

echo
echo "=== Puertos de escucha (posible exfiltración) ==="
# Los keyloggers necesitan enviar los datos ("Phone home").
# Buscamos conexiones abiertas o puertos escuchando.

if command -v ss >/dev/null 2>&1; then
    # ss es el reemplazo moderno de netstat.
    # -l (listening), -t (tcp), -n (numeric), -p (mostrar proceso dueño del puerto).
    ss -ltnp | sed -n '1,200p'
elif command -v netstat >/dev/null 2>&1; then
    # Fallback a netstat si ss no existe.
    netstat -tulpn | sed -n '1,200p'
else
    echo "No se encontró ss/netstat"
fi

echo
echo "=== Archivos comunes y rutas temporales (buscar keylogs) ==="
# Los keyloggers a menudo guardan los logs en carpetas donde cualquier usuario tiene permisos de escritura
# para evitar problemas de permisos (ej: /tmp, /dev/shm).
# -maxdepth 3: No buscar demasiado profundo para no tardar una eternidad.
find /tmp /var/tmp /dev/shm -maxdepth 3 -type f -iname "*keylog*" -o -iname "*keylogs*" -o -iname "*keystroke*" -print 2>/dev/null

echo
echo "=== Sugerencia: revisar manualmente PID mostrados con 'ls -l /proc/<PID>/exe' y 'readlink /proc/<PID>/exe' ==="