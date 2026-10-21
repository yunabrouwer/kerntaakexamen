# Design Document: TableTalk (Tafel Feedback Systeem)

## 1. Inleiding & Scope

### 1.1 Aanleiding
TableTalk helpt gasten op een eenvoudige manier feedback te geven over hun restaurantbezoek. Lange online vragenlijsten kosten te veel tijd. Met TableTalk kan een gast via een QR-code aan tafel een korte survey invullen zonder account.

### 1.2 Doelstelling
Het doel is een gebruiksvriendelijke, veilige en mobielvriendelijke webapplicatie te ontwikkelen. Gasten kunnen snel feedback geven; vestigingsmanagers kunnen de resultaten bekijken, gemiddelde scores analyseren en de opvolging van feedback registreren.

### 1.3 Doelgroepen
- **Gasten**: Vullen zonder account een korte vragenlijst in op hun telefoon.
- **Vestigingsmanagers**: Loggen in om feedback en statistieken van hun eigen vestiging te bekijken en beheren.

### 1.4 Scope
Het prototype omvat de publieke survey, bedankpagina, inlogpagina, dashboard, feedbackdetails, filters, feedbackstatussen en de database. De oorspronkelijke planning ging uit van circa 42 ontwikkeluren; dit is een planningsinschatting en geen bewijs van daadwerkelijk bestede uren.

---

## 2. Interfaceontwerp (Wireframes & UI)

Alle pagina’s vormen één ontwerp met een consistente huisstijl: een rustige witte basis, donkergroene accenten, duidelijke knoppen en voldoende witruimte.

### 2.1 Survey voor de gast (Mobile First)
- **Elementen**: Gecentreerd TableTalk-logo, subtiele vestigingsnaam, vijf aanklikbare sterren, een categorie-dropdown en een toelichtingsveld.
- **Scorekleuren**: 
  - 1–2 sterren: Rood
  - 3 sterren: Oranje/geel
  - 4–5 sterren: Groen (`#2E7D32`)
- **Categorieën**: `Eten`, `Bediening` en `Sfeer`.
- **Validatie**: Alle drie de onderdelen zijn verplicht; foutmeldingen verschijnen bij het specifieke veld.

### 2.2 Bedankpagina
- Bevestigt alleen een inzending die daadwerkelijk succesvol is opgeslagen.
- Toont een vinkje, de tekst *"Bedankt voor je feedback!"* en een korte vriendelijke afsluiting.

### 2.3 Inlogpagina manager
- Input van e-mailadres en wachtwoord.
- Bij onjuiste gegevens verschijnt een generieke foutmelding zonder te onthullen welk veld onjuist was.

### 2.4 Dashboard manager
- Toont de gemiddelde score van de vestiging (op één decimaal).
- Tabel met kolommen: `Datum`, `Score`, `Categorie` en `Status`.
- **Filters**: Alle feedback, negatieve scores (1–2 sterren), nieuwe feedback en feedback van deze week.
- Link naar een vergelijking van actieve vestigingen met geaggregeerde aantallen, scoreverdelingen en gemiddelden; individuele feedback en toelichtingen blijven afgeschermd.
- De uitlogknop staat rechtsboven in de header.

### 2.5 Detailweergave feedback
- **Desktop**: Layout verdeeld in 70% breedte voor de toelichting en 30% voor metadata.
- **Mobiel**: Onderdelen worden onder elkaar gestapeld.
- **Acties**: De manager kan de status aanpassen naar `Gelezen` of `Actie ondernomen`. IP-adressen worden niet standaard opgeslagen of getoond.

---

## 3. Functionele Eisen (FE)

| ID | Feature | Doel / Input / Uitvoer | Foutafhandeling |
| :--- | :--- | :--- | :--- |
| **FE-01** | Survey openen via QR | **Invoer:** Gast opent URL met `vestiging_id`. <br>**Uitvoer:** Toon survey van actieve vestiging. | Bij onbekende/inactieve vestiging verschijnt een melding; verzenden geblokkeerd. |
| **FE-02** | Sterrenscore selecteren | **Invoer:** Gast kiest 1 t/m 5 sterren. <br>**Uitvoer:** Sterren krijgen de bijbehorende scorekleur. | Server controleert strikt het bereik 1–5; ongeldige invoer wordt afgewezen. |
| **FE-03** | Categorie selecteren | **Invoer:** Gast kiest Eten, Bediening of Sfeer. <br>**Uitvoer:** Categorie wordt gekoppeld aan record. | Ontbrekende of onbekende categorie wordt afgewezen. |
| **FE-04** | Toelichting invullen | **Invoer:** Gast voert tekst in. <br>**Uitvoer:** Tekst wordt veilig opgeslagen en getoond. | Lege of te lange tekst wordt afgewezen; uitvoer wordt ge-escaped tegen XSS. |
| **FE-05** | Feedback versturen | **Invoer:** Klik op verzendknop. <br>**Uitvoer:** Data wordt opgeslagen, doorsturen naar bedankpagina. | Bij opslagfout verschijnt een melding; geen valse succesbevestiging. |
| **FE-06** | Bedankpagina tonen | **Uitvoer:** Toon vriendelijke succesbevestiging. | Voorkom dat verversen van de pagina een tweede database-inzending aanmaakt. |
| **FE-07** | Manager inloggen | **Invoer:** E-mailadres en wachtwoord. <br>**Uitvoer:** Start beveiligde sessie en open dashboard. | Algemene foutmelding bij onjuiste gegevens; inlogpogingen kunnen worden gelimiteerd. |
| **FE-08** | Feedbackoverzicht | **Uitvoer:** Tabel met datum, score, categorie en status van de gekoppelde vestiging. | Bij geen resultaten verschijnt een lege status; databasefouten verbergen details. |
| **FE-09** | Gemiddelde score | **Uitvoer:** Gemiddelde score van de eigen vestiging tonen op één decimaal. | N.v.t. |
| **FE-10** | Feedback filteren | **Invoer:** GET-filter `filter` met waarde `all`, `negative`, `open` of `week`. <br>**Uitvoer:** Gefilterde dataset voor de eigen vestiging. | Onbekende filterwaarden worden veilig genegeerd (fallback naar alle feedback). De week loopt van maandag tot en met zondag. |
| **FE-11** | Details bekijken | **Invoer:** Feedback-ID via geselecteerde regel. <br>**Uitvoer:** Toon alle metadata en volledige tekst. | Niet-bestaand of onbevoegd record wordt geweigerd (403/404). |
| **FE-12** | Markeren als gelezen | **Invoer:** Klik op 'Gelezen'. <br>**Uitvoer:** Status in de DB bijwerken en UI verversen. | Bij mislukte update verschijnt een melding; oude status blijft behouden. |
| **FE-13** | Actie registreren | **Invoer:** Klik op 'Actie ondernomen'. <br>**Uitvoer:** Status bijwerken in database. | Bij mislukte update verschijnt een melding; optie tot opnieuw proberen. |
| **FE-14** | Koppelen aan vestiging | **Invoer:** `vestiging_id` uit URL of formulier. <br>**Uitvoer:** Feedback linken aan de juiste vestiging. | Server valideert het ID; hidden fields en parameters worden niet blind vertrouwd. |
| **FE-15** | Data-afscherming | **Invoer:** Ingelogde manager vraagt data op. <br>**Uitvoer:** Alleen data van de gekoppelde vestiging tonen. | Onbevoegde cross-tenant aanvragen worden direct door de server geweigerd. |
| **FE-16** | Responsive weergave | **Uitvoer:** Survey en dashboard schalen vloeiend mee op mobiel, tablet en desktop. | Layout mag niet breken of buiten het scherm vallen. |

---

## 4. Gebruikersflows & Logica

### 4.1 Flow van de gast
1. Gast scant de QR-code op de tafel (`URL?vestiging_id=X`).
2. Server controleert of de vestiging bestaat en actief is.
3. De webpagina toont de survey.
4. Gast selecteert een score, kiest een categorie en typt de toelichting.
5. Gast klikt op **'Feedback versturen'**.
6. Server valideert de gegevens en controleert de vestigingsstatus opnieuw server-side.
7. De feedback wordt veilig opgeslagen in de database.
8. De gast wordt doorgestuurd naar de bedankpagina.

**Alternatieve scenario's & edge cases:**
- *Verplicht veld mist*: Toon foutmelding bij het specifieke veld en behoud de reeds ingevulde waarden.
- *Vestiging inactief/bestaat niet*: Toon direct een melding dat de survey momenteel niet beschikbaar is.
- *Database offline*: Toon een algemene foutmelding en bied een 'opnieuw proberen'-knop aan.
- *Double-submit*: Schakel de verzendknop direct na de eerste klik uit via JavaScript om dubbele inzendingen te voorkomen.

### 4.2 Flow van de manager
1. Manager opent de inlogpagina en voert inloggegevens in.
2. Server valideert de credentials, accountstatus en haalt de gekoppelde `vestiging_id` op.
3. Dashboard opent en toont het scoregemiddelde en de feedbacklijst van de eigen vestiging.
4. Manager kan de lijst optioneel filteren via de GET-parameters.
5. Manager klikt een feedbackrecord open naar de detailweergave.
6. Manager leest de feedback en wijzigt de status naar `Gelezen` of `Actie ondernomen`.
7. Server controleert de autorisatie en slaat de statuswijziging op.
8. Na een statuswijziging keert de manager terug naar het feedbackoverzicht; uitloggen beëindigt de sessie en leidt naar de loginpagina.
9. De manager kan een vergelijking van vestigingen openen met alleen geaggregeerde cijfers; individuele feedback van andere vestigingen blijft afgeschermd.

---

## 5. Technisch Ontwerp & Datamodel

### 5.1 Technologie Stack
- **Backend**: PHP 8.x (voor alle serverlogica).
- **Database**: MySQL via **PDO** met **Prepared Statements**.
- **Frontend**: HTML5, CSS3 (Responsive / Mobile First).
- **Client-side Logica**: Vanilla JavaScript (voor sterren-interactie en directe formuliervalidatie).
- **Versiebeheer**: Git.

### 5.2 Databaseschema (ERD Specificatie)

#### Tabel: `vestigingen`

| Veld | Datatype | Sleutel | Uitleg |
| :--- | :--- | :--- | :--- |
| `id` | INT | PK | Uniek vestiging-ID (Auto Increment) |
| `naam` | VARCHAR(100) | | Naam van de horecavestiging |
| `actief` | BOOLEAN | | Status van de vestiging (1 = Actief, 0 = Inactief) |

#### Tabel: `admins`

| Veld | Datatype | Sleutel | Uitleg |
| :--- | :--- | :--- | :--- |
| `id` | INT | PK | Uniek manager/admin account-ID |
| `vestiging_id` | INT | FK | Koppeling naar `vestigingen.id` |
| `naam` | VARCHAR(100) | | Volledige naam van de manager |
| `email` | VARCHAR(255) | UNIQUE | Inlogadres (moet uniek zijn) |
| `wachtwoord_hash` | VARCHAR(255) | | Veilig gehasht wachtwoord via `password_hash()` |
| `actief` | BOOLEAN | | Status van het account |

#### Tabel: `feedback`

| Veld | Datatype | Sleutel | Uitleg |
| :--- | :--- | :--- | :--- |
| `id` | INT | PK | Uniek feedback-ID |
| `vestiging_id` | INT | FK | Koppeling naar `vestigingen.id` |
| `score` | TINYINT UNSIGNED| | Integers tussen 1 t/m 5 |
| `categorie` | ENUM | | Opties: `'Eten'`, `'Bediening'`, `'Sfeer'` |
| `toelichting` | TEXT | | De geschreven feedback van de gast |
| `status` | ENUM | | Opties: `'Nieuw'`, `'Gelezen'`, `'Actie ondernomen'` |
