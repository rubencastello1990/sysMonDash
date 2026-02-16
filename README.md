## sysMonDash - Systems Monitor Dashboard (Fork con soporte Zabbix 7.2)

---

**sysMonDash** es un panel de monitorización optimizado para entornos con un alto número de elementos a monitorizar mostrando aquellos eventos que requieran de atención.

Los backend soportados son Nagios, Icinga, Zabbix y Check_MK.

Es posible utilizar Nagios o Icinga mediante el plugin 'mk_livestatus' (recomendado) o el archivo 'status.dat'.

### Requisitos

* **PHP 7.4** o superior
* Servidor web (Apache, Nginx, etc.)
* Backend de monitorización: Zabbix (2.2 - 7.2), Nagios, Icinga o Check_MK

### Compatibilidad con Zabbix

| Versión Zabbix | API soportada | Autenticación |
|----------------|---------------|---------------|
| 2.2.x | V225 | Usuario/Contraseña |
| 2.4.x - 6.x | V245 | Usuario/Contraseña |
| **7.0 - 7.2** | **V720** | **API Token (Bearer) o Usuario/Contraseña** |

### Autenticación por API Token (Zabbix 5.4+)

Zabbix 5.4+ permite crear API tokens desde *Administración > Tokens de API*. Esto es más seguro que usar credenciales de usuario:

1. En Zabbix, ir a **Administración > Tokens de API > Crear token de API**
2. Asignar un usuario y opcionalmente una fecha de expiración
3. Copiar el token generado
4. En sysMonDash, seleccionar versión **7.2** y pegar el token en el campo **API Token de Zabbix**
5. Los campos usuario/contraseña se pueden dejar vacíos si se usa API token

Las funcionalidades de **sysMonDash** son las siguientes:

* Selección de múltiples backends.
* Filtrado de hosts a mostrar en vista principal
* Filtrado de servicios para NO mostrar en vista principal
* Selección de elementos críticos para mostrar siempre
* Detección de paradas programadas que se hayan establecido, así como su visualización en la vista principal
* Enlace con backends remotos mediante API JSON

---

### Instalación

Es necesario disponer de un servidor web con **PHP 7.4+** y el plugin MK livestatus o la API de Zabbix correctamente configurados en el sistema de monitorización.

Descargar la aplicación desde https://github.com/rubencastello1990/sysMonDash y descomprimirla en la ruta deseada (publicada por el servidor web).

Acceder a http://tuservidor.com/sysMonDash/config.php y configurar según tu entorno.

---

**sysMonDash** is an optimized monitoring dashboard for large environments which have a large number of items to monitor by showing those events that requires an special attention.

The supported backends are Nagios, Icinga, Zabbix and Check_MK.

It's possible to use Nagios or Icinga through the 'mk_livestatus' plugin (recommended) or the 'status.dat' file.

### Requirements

* **PHP 7.4** or higher
* Web server (Apache, Nginx, etc.)
* Monitoring backend: Zabbix (2.2 - 7.2), Nagios, Icinga or Check_MK

### Zabbix API Token Authentication (Zabbix 5.4+)

Instead of username/password, you can use API tokens created in *Administration > API Tokens*. In sysMonDash, select version **7.2** and paste the token in the **Zabbix API Token** field.

The **sysMonDash** key features are:

* Multiple backends selection.
* Hosts filtering to be shown in the main view
* Services filtering to NOT be shown in the main view
* Critical items selection to be always shown
* Scheduled downtimes detection and showing them in the main view
* Link to remote backends through JSON API

---

### Installation

You need to have a running **PHP 7.4+** webserver and setup the MK livestatus plugin or Zabbix API in the monitoring server.

Download the application from https://github.com/rubencastello1990/sysMonDash and unpack it in the public webserver root.

Point to http://yourserver.com/sysMonDash/config.php and set it according to your environment.

---

**DEMO: http://sysmondash.cygnux.org** (No disponible / Not available)

https://github.com/nuxsmin/sysMonDash

http://cygnux.org


![Main View](http://cygnux.org/wp-content/uploads/2016/02/sysMonDash_v1-624x338.png)

![Config View](http://cygnux.org/wp-content/uploads/2016/02/sysMonDash_v1_config-773x1024.png)



