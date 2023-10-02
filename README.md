# Warframe Extra Framework

Author: Flytachi

Naming extra framework for rapid deployment of projects: microservices, monolithic programs and software. 
Deployment environment
1. Clean deployment nginx, apache server. 
2. Deployment in docker compose and swarm.

Framework uses MVC pattern, code style similar to Java Spring.

### The structure of the ideal chain: 

Controller -> Service -> Repository -> Model

* Controller - represents the description of the client request, checks its data and performs validation.
* Service - represents business logic.
* Repository - is the bridge of communication with the database.
* Model - representation of a column in the database table (entity).

### Additional options:

* Wrapper - represents additional data wrapping (pagination). 
* CDO - database connection. 
* BKB - SQL query generator (where). 
* Request - client data object. Checks validity and typing of data.


## Quick start
* In the directory of the provost, create a folder 'app'.
* Download the 'Extra' repository to the 'app' folder.
* Then run the build script 'php app/Extra/\_\_build\_\_'.


## Справочник

-------------------------------------------
`extra` - Console command
-------------------------------------------
        php extra [args...] -[flags...] --[options...]
-------------------------------------------
