Dokumentacja
============

Skladowe klasy
--------------
1. metoda wyciagajaca glowne grupy ofert (np. miasta)
2. metoda wyciagaja oferty dla podanej grupy
Metoda musi zwrocic liste obiektow zawierajaca przynajmniej dwie informacje:
  - id
  - link 


Szczegoly oferty
----------------
  - identyfikator
  - tytul
  - ilosc sprzedanych kuponow
  - cena (oryginalna/promocja)
  - status (aktywna/wyprzedana)
  - data waznosci
  - link

Cykl zycia
----------
1. pobieramy grupy ofert dla portalu (np. miasta)
2. z bazy wyciagamy oferty, ktore w danej grupie powinny byc jeszcze aktywne
3. ze strony pobieramy dostepna liste ofert dla danej grupy
4. weryfikujemy, czy pojawily sie nowe oferty
4.1. jesli tak pobieramy o nich informacje
5. aktualizujemy informacje o trwajacych ofertach:
  - ilosc sprzedanych kuponow
  - status (aktywna/wyprzedana)

Zabezpieczenia
--------------
  - jesli dla nowej oferty nie jestesmy w stanie odszuakc wszystkich informacji
  moze to oznaczac, ze zmienil sie format strony -> rzucamy blad i nie
  aktualizujemy danych
  - jesli dla posiadanej oferty nie jestesmy w stanie uaktualnic informacji
  -> rzucamy blad, tak jak wyzej
 



Cel dzialania
=============

1. Wykres ilosci ofert w serwisie. Porownanie serwisow
  |    _____          ___/\_ Citeam
  |___/     \    ____/  ____ Groupon
  |          \__/     _/
  | __/\    _/\______/
  |/    \__/
  |_________________________

2. Wykres kumulacyjny sprzedawanych kuponow z podzialem na regiony
                        ____ Krakow
  |       _____________/  __ Warszawa
  |      / ______________/
  | ____/ /
  |/_____/       ___________ Poznan
  |/ ___________/
  |_/_______________________

3. Ilosc generowanej kasy (ilosc kupunow*wartosc) z podzialem na regiony
 3.1 Ilosc zarabianej kasy po sprawdzeniu prowizji 

4. Najpopularniejsza oferta sposrod wszystkich wystawionych
