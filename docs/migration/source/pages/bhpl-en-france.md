# bhpl-en-france — page source de mtbrabant.com

- **URL source (encodée)** : https://www.mtbrabant.com/bhpl/bhpl-en-france/
- **URL source (lisible)** : https://www.mtbrabant.com/bhpl/bhpl-en-france/
- **Réponse HTTP** : **302 Moved Temporarily** — la page n'a **pas** été servie
- **Capturée le** : 2026-08-20
- **Taille du corps reçu** : 0 octet (réponse sans corps)
- **SHA-256 du corps reçu** : `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855`
  (SHA-256 de la chaîne vide — il n'y a rien à hacher)
- **Espaces insécables (U+00A0) conservées dans ce fichier** : 0

> **Aucun contenu n'a pu être capturé pour cette page.** Elle est protégée par mot de passe sur le
> site source. Rien n'est reconstitué ici : ni de mémoire, ni depuis une autre page, ni depuis le
> titre de l'entrée de menu. Conformément au brief, une page inatteignable est un fait à signaler,
> pas un trou à combler.

## Ce que le serveur répond

Requête `curl` sans cookie, puis rejouée avec le cookie de session obtenu sur `/bhpl/` : **même
réponse 302 dans les deux cas.** En-têtes reçus le 2026-08-20 (la valeur du cookie de session change
à chaque requête) :

```
HTTP/1.1 302 Moved Temporarily
Content-Type: text/html
Transfer-Encoding: chunked
Connection: keep-alive
X-WS-Origin: available
X-WS-RateLimit-Limit: 1000
X-WS-RateLimit-Remaining: 999
Date: Thu, 20 Aug 2026 06:52:54 GMT
Server: Apache
Expires: Thu, 19 Nov 1981 08:52:00 GMT
Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0
Pragma: no-cache
Set-Cookie: DIY_SB=<identifiant de session, variable>; path=/;SameSite=None; secure
Location: https://www.mtbrabant.com/protected/?comeFrom=%2Fbhpl%2Fbhpl-en-france%2F
```

En suivant la redirection (`curl -L`), on obtient **200** sur
`https://www.mtbrabant.com/protected/?comeFrom=%2Fbhpl%2Fbhpl-en-france%2F` — 29 049 octets. Ce
n'est pas la page demandée, c'est le formulaire de mot de passe du gabarit IONOS. Sa zone
`diywebMain`, reproduite ici comme **preuve** et non comme contenu de la page :

```
Zone protégée par mot de passe

Cette page n'est accessible qu'avec un mot de passe valide.

Votre mot de passe :
```

Le formulaire porte les champs `password`, `do_login` (valeur `yes`) et un bouton `Connexion`.

## Contenu principal

**Non capturé — page protégée par mot de passe.** Aucune ligne ne sera écrite ici sans une capture
réelle.

## Ce qui est établi malgré tout

- L'entrée de menu existe : le sous-menu « BHPL » de **toutes** les pages capturées porte
  `[LIEN href=https://www.mtbrabant.com/bhpl/bhpl-en-france/]BHPL en France[/LIEN]`, en avant-dernière
  position, juste avant « Littérature ». C'est la seule chose que le site source dit de cette page
  sans mot de passe : **son libellé de menu**.
- L'URL **n'est pas au `sitemap.xml`** du site (52 `<loc>`, vérifié le 2026-08-20 : aucune ne contient
  `bhpl-en-france`). C'est donc une **53ᵉ URL**, hors du décompte de 52 de `BRIEF.md` §7.

## Conséquence pour la reprise

Cette page ne peut pas être migrée en l'état. Deux décisions appartiennent à l'éleveuse, aucune ne
peut être prise ici :

1. Le mot de passe, pour que la page soit capturée puis reprise.
2. Ou bien le constat que la page n'a pas à être reprise — auquel cas il reste à décider ce que
   devient l'entrée de menu « BHPL en France » et l'ancienne URL.
