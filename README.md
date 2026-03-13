# vibecodingFavoritos (Feb 2026)
### Comparativa de Vibe Coding vs. Humano en control de marcadores

Este es uno de los experimentos semanales que realizo en vibecodingmexico.com  

Este repositorio es el resultado de un experimento de vibecoding **Enfocado a empresas medianas LATAM 2026** realizado el 4 de febrero de 2026. La misión: crear un sistema de favoritos estilo Metro, seguro y ligero, optimizado para redes inestables (como el metro de la CDMX) y servidores cPanel.

## ⚖️ Sobre la Licencia
He elegido la **Licencia MIT** por su simplicidad. Es lo más cercano a una "Creative Commons" para código: haz lo que quieras con él, solo mantén el crédito del autor. 

* **¿Por qué no LGPL 2.1?** Aunque es una gran licencia para proteger mejoras (obligando a compartir los cambios del archivo), para este experimento buscaba la mínima fricción posible. La MIT es "Plug & Play", igual que la filosofía del proyecto.

  Pero uno de los archivos, el de minimax, si está bajo LGPL en un repositorio llamdo LEMkotir
  https://github.com/AlfonsoOrozcoAguilarnoNDA/lemkotir/blob/main/favoritos.php

## ✍️ Acerca del Autor
Este proyecto forma parte de una serie de artículos en **[vibecodingmexico.com](https://vibecodingmexico.com)**. Mi enfoque no es la programación de laboratorio, sino la **Programación Real**: aquella que sobrevive a servidores compartidos, bloqueos de oficina y conexiones de una sola rayita de señal.

Mi nombre es Alfonso Orozco Aguilar, soy mexicano, programo desde 1991 para comer, y no tengo cuenta de Linkedin para disminuir superficie de ataque. Llevo trabajando desde que tengo memoria como devops / programador senior, y en 2026 estoy por terminar la licenciatura de contaduria. En el sitio esta mi perfil de facebook.

[Perfil de Facebook de Alfonso Orozco Aguilar](https://www.facebook.com/alfonso.orozcoaguilar)

## 🛠️ ¿Por qué cPanel y PHP?
Elegimos **cPanel** porque es el estándar de la industria desde hace 25 años y el ambiente más fácil de replicar para cualquier profesional. 
* **Versión de PHP:** Asumimos un entorno moderno de **PHP 8.4**, pero por su naturaleza procedural, el código es confiable en cualquier hospedaje compartido con **PHP 7.x** o superior. Tu respaldo es como un "Tupperware" que puedes cambiar de refrigerador sin problemas.

---

## 📂 Guía de Archivos (Los Especímenes)

* **`config-sample.php`**: El molde de seguridad. Define la lista blanca de IPs y la conexión centralizada.
* **`database.sql`**: Script SQL con `DROP TABLE IF EXISTS` para crear la estructura en un clic.
* **`favoritoshumano.php`**: (620 líneas + licencia) La versión curada por el editor. Incluye control de caché, resiliencia de red y diseño Metro real. Basada en una respuesta de Copilot (imagen) y modificada por Gemini de hace 6 meses, pulida por experiencia humana.
* **`favoritosgemini.php`**: El equilibrio. Metódico, no ambicioso y el más fiel al archivo de configuración.
* **`favoritosclaude.php`**: El "Búnker". 900+ líneas de sobreingeniería. Impresionante visualmente pero con desobediencia funcional (no funciona el CRUD de categorías).
* **`favoritosgrok.php`**: El genio rebelde. Inestable al inicio (Error 500), pero impresionante en estética Metro tras el regaño.
* **`favoritoscopilot_v1.php`** / **`_v2.php`**: El becario. Códigos truncados por límites de tokens. Inútiles para despliegue directo.
* **`favoritosminimax.php`**: FUncional, con categorias colapsables y apariencia excelente. El mejor de los LLM

---

## 🤖 El Prompt Original (La Prueba)
Para que el experimento sea replicable, este fue el comando enviado a todas las LLMs:

INICIA PROMPT

Buenos días, necesito crear un control de favoritos en PHP; no sé qué versión tengo. Quiero que todo quede en una sola página y que verifiques la dirección IP; si no es la correcta, que me pida una contraseña hardcoded.

La palabra hardcoded es "gotham4feb*" y yo cambio el código cuando haga falta cambiarla.

Ya tengo la estructura de la base de datos. Técnicamente uso PHP, Bootstrap y Font Awesome. Quiero que sea procedural, que esté en una sola página, que me permita manejar categorías, elegir icono y color, usando una interfaz "Metro" para poder usarlo en celulares. Queremos algo que te pida contraseña si no es la IP correcta.

Para la prueba, queremos verlo sin contraseña. Que maneje categorías de favoritos. Altas, bajas y cambios de favoritos y categorías. Para que sea simple, en una sola página. No necesitas "buscar" porque eso lo haces en el navegador con Control+F.

Como adorno, que nos dé la posibilidad de elegir color e icono de cada categoría y que lo organice en orden alfabético. Usa, por favor, un pie de página fijo que nos diga la versión de PHP completa y la barra de navegación superior debe tener links a las diferentes partes. Incluye una opción de "Salir" (si entramos por contraseña).

Además de config.php, que todo quede en un solo archivo. ¿Te doy la estructura de la base de datos? Tú me dices cuándo te dé la estructura y mi archivo config.php. ¡Gracias!

FIN DE PROMPT

Nota. A propósito no usé bootstrap 4.6 pero por lo general lo especifico.

---

## 🖼️ Evidencia Visual
Las imágenes de las interfaces generadas se encuentran en la carpeta del repositorio para su consulta. Verás la diferencia entre el "Bootstrap genérico" de la IA y el "Mosaico Sólido" del diseño humano.

Revisa el de Minimax, es sorprendente.

## 🚀 Requisitos Mínimos
1. Un dominio y hospedaje php 7.x Hospedaje compartido con PHP 7.x o superior y acceso a MySQL/MariaDB.
