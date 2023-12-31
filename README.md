# Workshop

1. Sprendžiamo uždavinio aprašymas.

1.1. Sistemos paskirtis: 

   Projekto tikslas – palengvinti dirbtuvėse darbus atliekančių darbuotojų kasdienybę, darbų registravimą, pasiskirstymą.
   
   Veikimo principas – kuriamą sistemą sudaro internetinė svetainė, kuria naudosis dirbtuvėse esantys darbuotojai ir dirbtuvių administratoriai.
   
   Dirbtuvės darbuotojas, norėdamas naudotis šia sistema, prisiregistruos prie internetinės svetainės ir gales susidaryti savo darbo dienotvarkės veiklas, pridėti sau darbus, redaguoti savo priskirtus darbus, pašalinti užduotis. Administratorius tvirtina dirbtuvės darbuotojų registracijas, peržiūri darbuotojų susidarytus dienotvarkės darbus, priskirti naujus, redaguoti esamus, pašalinti nereikalingus ar jau įvykdytus.

1.2. Funkciniai reikalavimai

Neregistruotas sistemos naudotojas galės: 

1.	Peržiūrėti pagrindinį puslapį; 
2.	Prisijungti prie internetinės svetainės;
3.	Peržiūrėti dirbtuves ir jų informaciją.

Registruotas sistemos naudotojas galės: 

1.	Atsijungti nuo internetinės svetainės; 
2.	Prisijungti (užsiregistruoti) prie internetinės svetainės; 
3.	Peržiūrėti darbuotojų sąrašą; 
4.	Peržiūrėti savo darbo užduotis (darbus); 
5.	Sukurti sau asmenines darbo užduotis; 
6.	Pridėti darbų aprašymą; 
7.	Redaguoti savo asmenines darbo užduotis; 
8.	Pašalinti savo asmenines užduotis; 
9.	Pažymėti darbus, kaip atliktus; 
10.	Peržiūrėti kitų dirbtuvės darbuotojų darbus. 

Administratorius galės: 

1.	Pridėti dirbtuvės darbuotoją į dirbtuvę; 
2.	Šalinti naudotojus; 
3.	Redaguoti naudotojus;
4.	Sukurti papildomus darbus darbuotojams; 
5.	Pašalinti darbuotojo darbus; 
6.	Redaguoti darbuotojo darbus; 
7.	Sukurti darbuotojo darbų aprašymą; 
8.	Redaguoti darbuotojo darbų aprašymą; 
9.	Šalinti darbuotojo darbų aprašymą; 


Sistemos architektūra:

Sistemos sudedamosios dalys:

1. Kliento pusė (angl. Front-End) – naudojant Vue.js technologiją;
2. Serverio pusė (angl. Back-End) – naudojant PHP Symfony framework;
3. Duomenų bazė – MongoDB.
   
Sistemos talpinimui naudojamas DigitalOcean serveris. Kiekviena sistemos dalis yra diegiama tame pačiame serveryje. Internetinę aplikaciją bus galima pasiekti per HTTP protokolą. Duomenų apsikeitimui su duomenų baze bus naudojamas Workshop API, kuris vykdo duomenų apsikeitimą naudojant ODM sąsają.
