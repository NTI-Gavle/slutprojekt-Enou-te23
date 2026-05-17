# Slutrapport - Enno Keanu Strand te-23
## Projektöversikt

**Projekt:** Enno Keanu Strand - En chat-applikation med gruppchatt, privata meddelanden och administratörsfunktioner.
---

## Implementerade Funktioner

### Användarsystem
- Registrering med e-post/lösenord (password_hash)
- Inloggning med sessionshantering
- Profil med avatar-uppladdning
- Online/offline-status

### Chattfunktioner
- **Gruppchatt:** Skapa publika/privata rum med anpassad rumskod
- **Privatchatt:** En-till-en-meddelanden med vänner
- **Filuppladdning:** Bilder/filer (max 4 filer, 5MB) med förhandsvisning
- **Realtid:** Polling-baserad meddelandeuppdatering

### Sociala Funktioner
- Vänssystem (skicka/ta emot/avvisa förfrågningar)
- Notifikationer med röd badge + "Olästa meddelanden"-sektion

### Adminfunktioner
- **Globala admin:** admin.php för att hantera alla användare/rum, varningar
- **Rumägare:** Kicka användare, befordra moderatörer, ändra rumskod/synlighet, ban
- **Moderatorer:** Ta bort meddelanden, kicka vanliga medlemmar

### Övrigt
- Hamburger-meny (Hem, Om oss, Juridiskt)
- Varningssystem (popup på varje sida tills bekräftad)
- Chattstatistik med Chart.js på profilsidor

---

## Teknisk Implementation

### Databas
- Tabeller: users, rooms, room_members, messages, friends, password_resets
- PDO med förberedda uttryck (säker mot SQL-injection)
- UTC tidszon

### Säkerhet
- XSS-skydd med htmlspecialchars()
- Lösenord hashing med password_hash()
- Sessionsvalidering på skyddade sidor

### Struktur
```
/config      - Konfiguration, Mailer
/database    - DB-anslutning, schema
/includes    - Header, sidebar, chatt-gränssnitt
/public      - Sidor, auth, API-endpoints
/uploads     - Uppladdade filer
```

---

## Avklarade Uppgifter

| Vecka | Moment | Status |
|-------|--------|--------|
| 13-14 | Grundlayout, inloggning, registrering | ✓ |
| 15 | Gruppchatt/privatchatt modals | ✓ |
| 16 | Databas, inloggning med PDO | ✓ |
| 17 | Meddelandehantering, profil | ✓ |
| 18 | AJAX uppdateringar, säkerhet | ✓ |
| 19-20 | Adminpanel, dokumentation, final testing | ✓ |

---

## Dokumentation

- **README.md** - Projektöversikt, funktioner, krav
- **FILES.md** - Fil-för-förklaring

---

## Möjliga Förbättringar

1. **Realtid** - Byta från polling till WebSockets för snabbare uppdateringar
2. **Push-notifikationer** - Web Push API för nya meddelanden
3. **Kryptering** - End-to-end kryptering för privata meddelanden
4. **Mobilapp** - React Native/PWA för bättre mobilupplevelse
5. **Filtyper** - Stöd för fler filtyper (dokument, ljud)

---

## Slutsats

Projektet uppfyller alla grundkrav och innehåller extra funktioner som:
- Avancerat admin-system med två nivåer (global/rum)
- Varningssystem
- Statistik med diagram
- Filhantering med förhandsvisning