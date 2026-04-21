# Workforce Tracker App

Aplicació web de control de presència per a empreses, desenvolupada com a projecte de l'assignatura d'Implementació d'Aplicacions Web.

## Descripció

Sistema de fitxatge d'entrades i sortides per a una empresa de 400 empleats. La verificació de presència es realitza mitjançant connexió VPN corporativa, ja que l'empresa no permet l'ús de geolocalització. Inclou suport per a teletreball, registre manual nocturn amb comentari obligatori i gestió per departaments amb diferents nivells d'accés segons el rol de l'usuari.

## Rols

### Empleat (usuari estàndard)
- Fitxar entrada i sortida mitjançant VPN
- Registre manual nocturn si s'ha oblidat fitxar durant el dia
- Consulta i descàrrega de nòmines mensuals
- Visualització del propi horari setmanal i dies de teletreball
- Consulta i registre d'hores als projectes assignats
- Sol·licitud i seguiment d'hores extres
- Consulta de notificacions i alertes
- Accés al directori de contactes de l'empresa
- Gestió del perfil personal

### Cap de departament
- Visualització en temps real de l'estat de presència del seu equip
- Revisió i validació de registres manuals nocturns
- Consulta del temps invertit per cada empleat en cada projecte
- Accés a resums setmanals: hores treballades, tasques completades, hores extres i incidències
- Modificació dels horaris dels empleats del seu departament
- Gestió de dies de teletreball dels membres de l'equip
- Aprovació o rebuig de sol·licituds d'hores extres i canvis de modalitat
- Accés a informes i analítiques del departament
- Enviament de comunicats i avisos a l'equip

## Departaments

- Direcció
- Desenvolupament
- Comptabilitat
- Recursos Humans (amb accés de supervisió global sobre tots els registres)

## Funcionalitats destacades

- Verificació de presència per VPN sense ús de geolocalització
- Excepció automàtica per a empleats en modalitat de teletreball
- Registre manual nocturn amb comentari obligatori i marcatge com a registre manual
- Recordatoris automàtics si la VPN està activa i no s'ha fitxat
- Registre i sol·licitud d'hores extres amb flux d'aprovació
- Exportació d'informes en PDF, CSV i Excel
- Disseny responsive amb navegació inferior en mòbil i lateral en escriptori

## Tecnologies

- Frontend: HTML + CSS + JavaScript
- Backend: PHP
- Base de dades: MySQL

## Autor

Awa Gikineh Konate — Curs 2025/2026