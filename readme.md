# Sollicitatie Zicht Online

## Werkwijze

Hieronder kunt u kort lezen welke overwegingen ik gemaakt heb bij het bouwen dit project.

Na het lezen van de probleemstelling heb ik nagedacht over welke aanpak ik zou gebruiken om de oplossing te realiseren. Al snel bedacht ik uit zou komen bij een microframework. Om alles in vanilla PHP te schrijven was geen optie omdat ik niet het wiel opnieuw wil uitvinden en het gebruik van één van de grotere frameworks zou ook weer overkill zijn voor een applicatie van deze omvang. 

Zodoende heb ik een klein onderzoekje gedaan welk microframework te gebruik. Van de meest populaire frameworks vielen er gelijk twee af: Silex (want is end-of-life) en Phalcon (Want te kleine userbase dus minder documentatie en ondersteuning). Hierdoor bleven Slim en Lumen over. Uiteindelijk heb ik gekozen voor Slim, omdat het weliswaar aanzienlijk minder features heeft dan Lumen, maar dat in dit geval ideaal past. Alle libraries die ik nodig heb zou ik as-needed toe kunnen voegen. Daarbij was het een kans om te etaleren dat ik begrip heb van de onderliggende architectuur (omdat ik meer zelf moet doen) en is het een mooie gelegenheid om iets nieuws te leren (aangezien dit mijn eerste Slim applicatie is).

Na de keuze voor het framework heb ik de database opgezet. Ik heb getwijfeld of ik migrations zou gaan gebruiken (Phinx) of simpelweg een SQL file zou toevoegen. Gezien de geringe grootte heb ik voor het tweede gekozen. Waarschijnlijk zou de API ook nooit leidend zijn bij het beheer van de database (vermoedelijk zou dit elders geregeld zijn), dus zou ik dit op een productieomgeving waarschijnlijk ook niet doen. 

Bij het ontwerp van de database heb ik nog getwijfeld of ik een auto incrementing ID zou gebruiken of het e-mailadres de sleutel zou maken, maar door een post op stack overflow (https://stackoverflow.com/questions/3804108/use-email-address-as-primary-key) ben ik overtuigd dat e-mail adressen met de tijd door een ander persoon gebruikt kunnen gaan worden en dat ze dus geenszins uniek hoeven te zijn. Dus heb ik voor een numerieke ID gekozen.

Verder heb ik een unieke database user met beperkte rechten toegevoegd in de SQL file, omdat dit veel veiliger is dan de root user te gebruiken die de hele database kan wijzigen. Nu kan de user slechts beperkte wijzigingen op tabel-niveau uitvoeren.

Voor het beheer van de database heb ik besloten een ORM te gebruiken. Voor deze specifieke usecase wellicht wat overdreven, maar in de toekomst zou dit zeker voor meer ontwikkelgemak zorgen. Daarnaast neemt de ORM een heleboel kopzorgen weg (bijvoorbeeld bij het escapen van data). Ik kwam uiteindelijk uit bij het (voor mij) oude vertrouwde Eloquent. Voornamelijk omdat het in de documentatie van Slim staat (en er dus vaker nagedacht is over de integratie). Ik ben me wel bewust dat deze ORM het active record patteern gebruikt en niet data mapper, waardoor de code wat meer verweven is met de library. Voor dit simpele voorbeeld leek mij dit prima. Op een productie omgeving had ik wellicht gekozen voor doctrine vanwege separation of concerns.

Voor de lokale configuratie heb ik Dotenv gebruikt. Hierdoor kan op iedere omgeving veilig een niet-ingecheckte configuratiebestand neergezet worden met wachtwoorden. Hierdoor staan de wachtwoorden niet in GIT.

Voor logging heb ik gebruik gemaakt van het meest populaire pakket: Monolog. De koppeling hiermee staat tevens uitstekend gedocumenteerd in de documentatie van Slim.

Tot slot heb ik PHPunit gebruikt voor het testen. Dit is de defacto standaard en ik kan daarmee testen wat ik wil, dus de keus was niet zo ingewikkeld.

Voor het ontsluiten van de API heb ik besolten om gebruik te maken met de standaard die omschreven is op http://www.jsonapi.org. Het is wellicht een wat grote standaard voor zo'n kleine API. Maar uit ervaring weet ik dat werken met een dergelijke standaard zowel aan de aanbiedende als aan de afnemende kant heel duidelijk maakt wat er geïmplementeerd dient te worden. Het voorkomt semantische discussies.

Slim heeft geen voorgeschreven architectuur en in de documentatie wordt zelfs gewerkt met een enkele PHP bestand. Gelukkig was er een skeleton applicatie waar ik een groot deel van de structuur van af kon leiden. Uiteraard heb ik er mijn eigen draai aangegeven want ik ben nou eenmaal een eigenwijze IT'er. 

Omdat dit een API is, is er niet echt een View laag, maar de Model en Controllers heb ik wel zo veel mogelijk gescheiden om de logica op de juiste plek te houden. Configuratie heb ik in een aparte map neergezet en deze maakt ook gebruik van omgevings variabelen voor lokale settings. 

Logging laat ik wegschrijven naar een bestand in een aparte map (storage). Je wilt immers weten wat er mis gaat en de einddebruiker hier niet aan blootstellen. In deze map worden tevens de route caches weggeschreven. Dit zorgt er voor dat de applicatie net een stukje sneller is.

In de "tests" map staan alle tests die alle mogelijke API-endpoints bestrijken. Naast de controllers is er verder niet zo veel te testen, dus leken me dit soort functionele tests het best. Aan het begin van iedere wordt een transactie gestart en wordt de database met test data gevuld, hierna volgt de test, en uiteindelijk wordt alle teruggerold zodat de database integriteit intact blijft. Normaliter zou het het beste zijn deze tests op een integratieserver uit te voeren om te zorgen dat er niet nodeloos de kans bestaat dat data wordt aangetast.

De documentatie van de API heb ik samengesteld op basis van de methodiek die omschreven is op https://www.apiblueprint.org. Het voordeel hiervan is dat als je de documentatie door een parser haalt het er gelijk fraai uitziet en het schrijven ervan in markdown kan gebeuren. Hoe het uiteindelijk geworden is, is hier te bekijken: https://paulkned.docs.apiary.io.

Al met al ben ik best tevreden over het eindresultaat, ook al heeft het me meer tijd gekost dan ik van plan was (ik moest immers ook terloops een nieuw framework leren). Ik hoop dat jullie het ook wat vinden!  

## Verbetervoorstellen
 
Een aantal zaken heb ik niet toegevoegd vanwege de scope en tijdsdruk van het project, het volgende zou ik echter bij een commercieel product wel hebben toegevoegd:
 
* Authenticatie/Authorisatie, op basis van een API-key of oAuth.
* Rate limiting, Nu kan iedereen onbeperkt de API spammen, dat kun je eenvoudig voorkomen door een rate limit toe te voegen per gebruiker.
* Validators extraheren: Ik vind nu zelf dat de validatie van de velden bij een POST en een PATCH een beetje slordig is. Idealiter zou ik ze hebben willen extraheren naar classes als generieke validators (zoals bijvoorbeeld een RequiredValidator en een EmailValidator). Dit is generieker en verhoogt de ontwikkelsnelheid in een later stadium.
* Paginatie in de lijst: Als je nu de lijst van alle aanmeldingen ophaalt is het één lange blob. Uit performance en overzichtsoverwegingen zou ik nog parameters toevoegen om de lijst te kunnen pagineren.

## Installatie

Om de applicatie te installeren, volg de volgende stappen:
1. Clone de repository.
2. Creëer een database (en optioneel een database user). En voeg de email_subscriptions tabel toe. De gehele SQL script staat in `artifacts/database.sql`
3. Ga naar de `src` map.
4. Voer `composer install` uit, indien u composer niet geïnstalleerd hebt, voer dan `php ../artifacts/composer.phar install` uit.
5. Kopieer `.env.example` naar `.env` en vervang de voorbeeld waardes met juiste instellingen.
6. De applicatie is klaar voor gebruik. Zie de rest van de documentatie voor een beschrijving van de service. Om tests uit te voeren: voer eenvoudigweg `./vendor/bin/phpunit` uit.

## API documentatie

De API documentatie kunt u hier bekijken: https://paulkned.docs.apiary.io