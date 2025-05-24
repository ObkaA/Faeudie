
# FAEUDIE

Projekt na kurs Bazy Danych 2025 - AIR PWR semestr 6.

## Restore i normalna praca
### PostgreSQL
Uruchomić serwer postgres:

```sudo systemctl start postgresql```

### PGAdmin
Uruchomienie PGAdmin przez wpisanie w terminalu:

``` /usr/pgadmin4/bin/pgadmin4``` 

### Wczytywanie backupa
Otworzyć query tool dla bazy danych

Wpisać dwie linijki:
DROP SCHEMA public CASCADE;
CREATE SCHEMA public;

W terminalu wpisać $ cp produkty_all_unique_names.csv /tmp/


I uruchomic ten skrypt.
Natępnie usunąć to i wkleić jako skrypt zawartość DB_BACKUP.backup i uruchomić skrypt.
Prawy nacisnąć refresh.

### PHP
Uruchomić serwer php:

```php -S localhost:8001```

(chyba port nie jest istotny)

Następnie wejść w przeglądarkę i wpisać:

```localhost:8001/index.php```

Powinna otworzyć się strona.


### Backup
Klikamy prawym na bazę i ```Backup```:

- Name - wybieramy DB_BACKUP.backup by go nadpisać (jeżeli go nie widać to przeklikać typy plików prawym dolnym rogu
- Format - Plain
- Querry Option -> Use Insert Command włączone

resztę zostawić i save. Gotowy plik proszę dorzucić commitem do repo.

## Konfiguracja (Pierwszy raz)
### Instalacje
zainstalować
PGAdmin
PostgreSQL
PHP
instrukcje do instalacji najlepiej z chatGPT.

### PostgreSQL
Uruchomić serwer postgres:

```sudo systemctl start postgresql```

następnie ustawić hasło:

```sudo -u postgres psql```

po pojawiniu się prompta wpisać
```\password postgres```
i ustawić hasło ```maslo555```

### PGAdmin

Uruchomienie PGAdmin przez wpisanie w terminalu:
``` /usr/pgadmin4/bin/pgadmin4``` 

kliknąć register server i tam wybrać:

- name - dowolne
- Host name - localhost
- password - to co ustawiane było wyżej

resztę rzeczy zostawić. Jeżeli chcemy innego użytkownia to trzeba dla niego ustawić hasło i tu też (ale też zmienić w plikach php więc dla nas nie warto)





