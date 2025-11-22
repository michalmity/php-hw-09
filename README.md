# Úkol 9: REST API

Vaším úkolem je vytvořit jednoduché REST API pro správu knih. Pomocí API lze prohlížet existující knihy, vytvářet nové a upravovat a mazat existující.

Jako persistentní úložitě budeme používat Sqlite a do něj budeme ukládat informace o knihách. K ukládání bude sloužit tabulka `books` a bude mít následující sloupce:

- `id`
- `name`
- `author`
- `publisher`
- `isbn`
- `pages`

Testovací prostředí **závisí** na konkrétních názvech sloupců; prosíme o jejich dodržení.

Prohlížení existujících záznamů může dělat kdokoliv. Operace, které záznamy upravují mohou dělat pouze autorizovaní uživatelé,
kdy ověření probíhá pomocí HTTP Basic Auth. Pro účely tohoto úkolu stačí "zahardcodovat" uživatele `admin` s heslem `pas$word`.
Pro získávání hesla a loginu použijte `$request->getHeader('Authorization')` např. v nějakém middlewaru.

Máte připravenou kostru aplikace v `public/index.php` a `src/Rest/RestApp`. Implementujte metodu `configure()` ve třidě `RestApp`, zbytek třídy neměnte.

Máte také připravenou dokumentaci endpointů v openapi specifikaci ve složce [`docs`](docs/openapi.yaml). Tu si můžete zobrazit v PHP stormu a nebo v libovolném editoru openAPI, třeba swagger.

Není potřeba implementovat vše v do jedné metody, máte nastavený namespace `Books` do složky `src`,
vytvořte si další třídy, které budete potřebovat, aby byl kód přehledný.


## Kontrola správnosti
Pro spuštení public testů stačí příkaz:

```sh
composer test
```

Samozřejmě, je zde opět předtím potřeba nainstalovat podpůrné knihovny, jak bylo ukázáno v předchozích úkolech.

Dále je možné se připojit k vaší restové službě, tu naleznete na adrese `localhost:80` za předpokladu, že používáte náš docker image.

---

## Seznam uložených knih

**Request**

```
> GET /books

```

**Success Response**

Vrátí seznam uložených knih. V případě, že žádné knihy uložené nejsou, vrátí prázdný seznam. Seznam knih obsahuje pouze `id`, `name` a `author`.

```
< 200 OK

[{
    "id": 1,
    "name": "The Little Prince",
    "author": "Antoine de Saint-Exupéry"  
}, {
    ...
}]
```

---

## Detail knihy

**Request**

```
> GET /books/:id

```

**Success Response**

Vrátí detail knihy, který obsahuje všechna pole.

```
< 200 OK

{
    "id": 1,
    "name": "The Little Prince",
    "author": "Antoine de Saint-Exupéry",
    "publisher": "Mariner Books",
    "isbn": "978-0156012195",
    "pages": 96
}
```

**Error Response**

V případě neexistujícího `id` vrátí HTTP chybu 404.

```
< 404 Not Found

```

V případě špatně zadaného `id` vrátí HTTP chybu 400 (např. není to číslo).

```
< 400 Bad Request

```

---

## Vytvoření nové knihy 🔐

**Request**

Novou knihu může vytvořit pouze autorizovaný uživatel. To je ověřeno pomocí HTTP Basic Auth. Je potřeba poslat všechny informace o knize.

```
> POST /books

Authorization: Basic <token>
Content-Type: application/json


{
    "name": "The Little Prince",
    "author": "Antoine de Saint-Exupéry",
    "publisher": "Mariner Books",
    "isbn": "978-0156012195",
    "pages": 96
}
```

**Success Response**

Server automaticky vygeneruje `id` nové knihy a vrátí hlavičku `Location`, která obsahuje URL nové knihy.

```
< 201 Created

Location: /books/:id
```

**Unauthorized Error Response**

Pokud uživatel nepošle správný token nebo ho nepošle vůbec, vrátí server HTTP chybu 401.

```
< 401 Unauthorized

```

**Bad Request Error Response**

Pokud request neobsahuje všechny informace o knize, vrátí server HTTP chybu 400. Pokud chcete, můžete v odpovědi vrátit i informace o chybějících datech (ve formátu JSON).

```
< 400 Bad Request

```

---

## Aktualizace existující knihy 🔐

**Request**

Aktualizovat existující knihu může opět pouze autorizovaný uživatel. Pošle všechny informace znovu a existující záznam je jimi zcela nahrazen, `id` zůstává stejné.

```
> PUT /books/:id

Authorization: Basic <token>
Content-Type: application/json


{
    "name": "The Little Prince",
    "author": "Antoine de Saint-Exupéry",
    "publisher": "Mariner Books",
    "isbn": "978-0156012195",
    "pages": 96
}
```

**Success Response**

V případě úspěchu server nic nevrací a odpoví HTTP statusem 204.

```
< 204 No Content

```

**Unauthorized Error Response**

Pokud uživatel nepošle správný token nebo ho nepošle vůbec, vrátí server HTTP chybu 401.

```
< 401 Unauthorized

```

**Not Found Error Response**

Pokud je uživatel správně autorizovaný, ale snaží se aktualizovat neexistující záznam, vrátí server HTTP chybu 404.

```
< 404 Not Found

```

**Bad Request Error Response**

Stejně jako v případě vytváření nové knihy, je i zde potřeba ověřit, že jsou odeslaná všechna data.

```
< 400 Bad Request

```

---

## Smazání knihy 🔐

**Request**

Knihu může smazat pouze autorizovaný uživatel.

```
> DELETE /books/:id

Authorization: Basic <token>

```

**Success Response**

V případě úspěchu server nevrací nic, odpoví HTTP statusem 204.

```
< 204 No Content

```

**Unauthorized Error Response**

Pokud uživatel nepošle správný token nebo ho nepošle vůbec, vrátí server HTTP chybu 401.

```
< 401 Unauthorized

```

**Not Found Error Response**

Pokud je uživatel správně autorizovaný, ale snaží se smazat neexistující záznam, vrátí server HTTP chybu 404.

```
< 404 Not Found

```

## Poznámky k úloze:
* Za žadnou cenu neměňte strukturu projektu. Úloha je hodnocena automaticky. Při úpravě struktury úlohy hodnocení neuspěje, a neobdržíte tak žádné body.
* V této úloze je možné si přidat další vlastní knihovny. I přes to však nemodifikujte knihovny, které jsou již v [composer.json](composer.json) jsou uvedeny, včetně jejich verzí. Nezapomeňte dodat vlastní [composer.lock](composer.lock).
* V rámci automatických testů je prováděno přímé připojení k databázy. Z toho důvodu je nutné zůstat u sqlite databáze a ponechat její soubor na umístění uvedeném v souboru [DB.php](src%2FDatabase%2FDB.php). Databázový soubor nenahrávejte do gitu.
* V souboru filelist.txt jsou vyjmenovány soubory, které nesmí být změněny. Jejich změna povede k zastavení scriptu ještě před začátkem hodnocení a neobdržíte žádné body. V případě tohoto problému doporučujeme soubory obnovit pomocí Gitu.
* Myslete na to, že testy jsou spouštěny v samostatných procesech mezi jednotlivými dotazy. Díky tomu, data která nebudou uložena v databázy po dokončení 1 requestu budou ztracena.
