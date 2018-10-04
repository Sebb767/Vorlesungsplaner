# Vorlesungsplaner

Dieses kleine Webanwendung erlaubt das exportieren von ausgewählten Vorlesungen an der FHWS im iCal-Format. Live unter [https://unicorns.diamonds/vp/](https://unicorns.diamonds/vp/).

## FAQ

### Wie geht die Suche?

Es werden Suchwörter aus dem Namen der Vorlesung, dem Semester, der ID und den Dozenten erstellt. Daher kann man mit z.B. `BIN2` nach allen Vorlesungen im Semester suchen. Man kann auch nach den Vorlesungen eines Dozenten suchen, bspw. indem man `Heinzl` sucht. Natürlich kann man auch nach dem Namen einer Vorlesung suchen.

### Wie kann ich mir meine Auswahl speichern?

Jedes mal wenn eine Vorlesung an- oder abgewählt wird, wird die URL automatisch geupdated. Daher kannst du einfach die aktuelle URL bookmarken.

### Kann man mithelfen?

PRs sind gerne gesehen!

### Welche APIs verwendet die Anwendung?

Die Vorlesungen kommen alle von der FIWIS-Api der FHWS.

### Warum AngularJS und PHP?

Ich bin mit AngularJS und PHP bereits relativ vertraut, abgesehen davon sind beide weit verfügbar und halten die Projektstruktur simpel (für Angular z.B. bräuchte man auch einen Compiler, installierte Dependencies etc).
