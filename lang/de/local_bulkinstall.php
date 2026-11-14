<?php
// This file is part of Moodle - https://moodle.org/
/**
 * File documentation for lang/de/local_bulkinstall.php.
 *
 * @package   local_bulkinstall
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license   See LICENSE.md for the full terms.
 */
$string['errorupdateproof'] = 'Der signierte Update-Nachweis ist ungültig, abgelaufen, unvollständig oder wird nicht mehr unterstützt. Kehre zum Plugin Manager zurück und bereite eine neue Auswahl vor.';
$string['errorupdateversion'] = 'Der Paket-Build stimmt nicht mit dem ausgewählten signierten Release überein.';
$string['errorupdatechanged'] = 'Die Installation wurde seit der Vorbereitung dieser Updates verändert. Bereite eine neue Update-Auswahl vor.';
$string['errorinstallbusy'] = 'Eine andere Installation im Bulk Installer läuft bereits. Warte, bis sie abgeschlossen ist, und versuche es erneut.';
$string['signedupdatesnotice'] = 'Diese Pakete wurden aus dem signierten Update-Katalog heruntergeladen. Hashes, Komponenten, Builds und Installationsstand werden vor der Installation erneut geprüft. Bestätige einmal, um den gesamten Paketsatz an Moodle zu übergeben, und schließe danach das Moodle-Upgrade ab.';
// This file is part of a 108design source-available software product.
//
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
//
// Use and modification are permitted only under the Software License included
// with this distribution. Redistribution and circumvention of Pro feature or
// licensing restrictions are prohibited. See LICENSE.md for the full terms.

$string['pluginname'] = 'Bulk Installer/Uninstaller';
$string['menulabel'] = 'Bulk Installer/Uninstaller';
$string['pagetitle'] = 'Bulk Installer/Uninstaller';
$string['uploadheading'] = 'Plugin-Pakete hochladen';
$string['uploadintro'] = 'Wähle bis zu 50 reguläre Moodle-Plugin-ZIPs oder ein Bundle-ZIP mit bundle.json und den Plugin-ZIPs direkt im ZIP-Stamm aus. Alle Pakete werden gemeinsam geprüft, bevor Plugin-Code verändert wird.';
$string['backupnotice'] = 'Sichere Website-Code und Datenbank, bevor Du eine größere Gruppe von Plugins installierst oder aktualisierst.';
$string['packages'] = 'Plugin-ZIP-Pakete';
$string['packages_help'] = 'Wähle mehrere reguläre Moodle-Plugin-ZIPs oder genau ein Bundle-ZIP aus. Ein Bundle kann nicht mit weiteren hochgeladenen Dateien kombiniert werden.';
$string['checkpackages'] = 'Pakete prüfen';
$string['bundleformatlink'] = 'Bundle-ZIP-Format und bundle.json-Spezifikation';
$string['bundleformattitle'] = 'Bundle-ZIP-Format';
$string['bundleformatintro'] = 'Ein Bundle ist ein äußeres ZIP mit bundle.json und allen aufgeführten regulären Moodle-Plugin-ZIPs direkt im ZIP-Stamm. Alle unterstützten Formatversionen sind strikt und erlauben keine weiteren Dateien oder Verzeichnisse.';
$string['bundleformatfilename'] = 'Signierte 108design-Bundles verwenden einen an Frankenstyle angelehnten kleingeschriebenen Namen wie bundle_kurswerkzeuge-1.0.0.zip. Für Format 2 ist das Präfix bundle_ verpflichtend; von Administratoren erstellte Format-1-Bundles bleiben auch ohne dieses Präfix kompatibel.';
$string['bundleformatstructureheading'] = 'ZIP-Struktur';
$string['bundleformatstructure'] = 'Alle Einträge sind Dateien direkt im ZIP-Stamm. Dateinamen beachten Groß- und Kleinschreibung und müssen exakt mit bundle.json übereinstimmen.';
$string['bundleformatmanifestheading'] = 'Beispiel für bundle.json';
$string['bundleformatfield'] = 'Feld';
$string['bundleformattype'] = 'JSON-Typ';
$string['bundleformatrequired'] = 'Erforderlich';
$string['bundleformatrules'] = 'Regeln';
$string['bundleformatruleformat'] = 'Muss moodle-plugin-bundle entsprechen.';
$string['bundleformatruleformatversion'] = '1 ist ein unsigniertes Kompatibilitäts-Bundle; 2 und 3 sind signierte 108design-Bundles. Version 3 ergänzt Aktivierungsmetadaten.';
$string['bundleformatruleactivation'] = 'Nur in der signierten Version 3 erforderlich. Steuert den Plugin-Manager-Wizard und verleiht niemals eine Lizenz.';
$string['bundleformatrulestoreproduct'] = 'Stabile Storefront-Produktkennung zum Finden passender Käufe im verbundenen Konto.';
$string['bundleformatruleentitlements'] = 'Erwartete kommerzielle oder kostenlose Entitlements und die dadurch abgedeckten Bundle-Komponenten.';
$string['bundleformatruleid'] = '2–100 ASCII-Kleinzeichen; beginnt mit einem Buchstaben, danach Buchstaben, Ziffern, Unterstrich oder Bindestrich.';
$string['bundleformatrulename'] = 'Nicht leere Zeichenfolge als Anzeigename mit höchstens 200 Zeichen.';
$string['bundleformatruleversion'] = 'Nicht leere Zeichenfolge als Bundle-Version mit höchstens 100 Zeichen. Dies sind Bundle-Metadaten, keine Moodle-Buildnummer.';
$string['bundleformatruledescription'] = 'Optionaler Text für Administratoren mit höchstens 2.000 Zeichen.';
$string['bundleformatruleplugins'] = 'Liste mit 1–50 Plugin-Objekten. Dateinamen und Komponenten müssen jeweils eindeutig sein; Dateinamen werden für die Eindeutigkeit ohne Beachtung der Groß-/Kleinschreibung verglichen.';
$string['bundleformatrulefile'] = 'Exakter Name eines ZIPs im Stamm mit höchstens 255 Zeichen; keine Pfad- oder Steuerzeichen.';
$string['bundleformatrulecomponent'] = 'Erwartete Moodle-Frankenstyle-Komponente, zum Beispiel mod_example. Sie muss der im Paket gefundenen Komponente entsprechen.';
$string['bundleformatrulesha256'] = 'Genau 64 Hexadezimalzeichen: SHA-256-Prüfsumme der unveränderten Bytes des enthaltenen ZIPs.';
$string['bundleformatstrict'] = 'Nur die dokumentierten Eigenschaften sind erlaubt. Jedes Plugin-ZIP im äußeren Archiv muss genau einmal aufgeführt sein und jedes aufgeführte ZIP muss existieren. Ein Bundle zusammen mit einer weiteren Datei wird abgelehnt.';
$string['bundleformatschema'] = 'Dieselbe verbindliche Spezifikation ist im Plugin unter schemas/bundle-v1.schema.json, schemas/bundle-v2.schema.json, schemas/bundle-v3.schema.json und docs/bundle-format.md enthalten.';
$string['backtoupload'] = 'Zurück zum Paket-Upload';
$string['bundledetected'] = 'Erkanntes Bundle';
$string['bundlearchive'] = 'Bundle-ZIP';
$string['bundleid'] = 'Bundle-ID';
$string['bundlename'] = 'Bundle-Name';
$string['bundleversion'] = 'Bundle-Version';
$string['bundledescription'] = 'Beschreibung';
$string['reviewtitle'] = 'Plugin-Gruppe prüfen';
$string['reviewintro'] = 'Prüfe die geplanten Änderungen und Ergebnisse, bevor Du die Gruppe an Moodle übergibst.';
$string['filename'] = 'Datei';
$string['component'] = 'Komponente';
$string['installedversion'] = 'Installierte Version';
$string['packageversion'] = 'Paketversion';
$string['release'] = 'Release';
$string['action'] = 'Aktion';
$string['validation'] = 'Prüfung';
$string['validationok'] = 'OK';
$string['errorprefix'] = 'Fehler: {$a}';
$string['warningprefix'] = 'Warnung: {$a}';
$string['actioninstall'] = 'Installieren';
$string['actionupdate'] = 'Aktualisieren';
$string['actionreinstall'] = 'Gleiche Version ersetzen';
$string['actiondowngrade'] = 'Downgrade';
$string['actionunknown'] = 'Unbekannt';
$string['reviewsummary'] = '{$a->count} Paket(e), insgesamt {$a->size} entpackt.';
$string['sequentialwarning'] = 'Moodle prüft zunächst alle Pakete, verteilt ihre Code-Verzeichnisse anschließend jedoch nacheinander. Dateiverteilung und folgende Datenbank-Upgrades bilden keine gemeinsame atomare Transaktion.';
$string['confirmationtext'] = 'Ich habe die aufgeführten Komponenten geprüft und möchte alle Pakete dieser Gruppe durch Moodle verteilen lassen.';
$string['installbatch'] = 'Pakete an Moodle übergeben';
$string['discardbatch'] = 'Gruppe verwerfen';
$string['batchblocked'] = 'Diese Gruppe kann nicht installiert werden. Korrigiere die Fehler und lade die Pakete erneut hoch.';
$string['batchchanged'] = 'Die zwischengespeicherte Gruppe besteht die Prüfung nicht mehr. Bitte prüfe sie erneut.';
$string['installingbatch'] = 'Plugin-Gruppe wird installiert';
$string['dependencyinbatch'] = 'Paketgruppe';
$string['dependencyinstalled'] = 'installierte Website';

$string['errornofiles'] = 'Wähle mindestens ein ZIP-Paket aus.';
$string['errortoomanypackages'] = 'Eine Gruppe darf höchstens {$a} Pakete enthalten.';
$string['errorcreatestaging'] = 'Das temporäre Arbeitsverzeichnis konnte nicht angelegt werden.';
$string['errorcopystagedfile'] = 'Das Paket „{$a}“ konnte nicht in den temporären Bereich kopiert werden.';
$string['errorstorageexpired'] = 'Diese zwischengespeicherte Gruppe ist abgelaufen oder gehört nicht zur aktuellen Sitzung.';
$string['errorinvalidstorage'] = 'Ungültige Kennung des temporären Speichers.';
$string['errorstagedfilemissing'] = 'Das zwischengespeicherte ZIP fehlt oder ist nicht lesbar.';
$string['errorintegritychanged'] = 'Das zwischengespeicherte ZIP wurde seit dem Upload verändert.';
$string['errorbundlemixedupload'] = 'Lade entweder ein Bundle-ZIP oder reguläre Plugin-ZIPs hoch. Ein Bundle kann nicht mit weiteren Dateien kombiniert werden.';
$string['errorbundleentries'] = 'Ein Bundle darf bundle.json und höchstens {$a} Plugin-ZIPs enthalten.';
$string['errorbundlerootonly'] = 'Jeder Bundle-Eintrag muss eine Datei direkt im ZIP-Stamm sein. Ungültiger Eintrag: {$a}';
$string['errorbundleduplicateentry'] = 'Das Bundle enthält einen doppelten oder nur in der Groß-/Kleinschreibung abweichenden Eintrag: {$a}';
$string['errorbundleunexpectedfile'] = 'Das Bundle enthält eine Datei, die weder bundle.json noch ein ZIP ist: {$a}';
$string['errorbundlemanifestmissing'] = 'Das Bundle enthält nicht genau eine bundle.json direkt im ZIP-Stamm.';
$string['errorbundlemanifestread'] = 'bundle.json konnte nicht sicher entpackt werden oder überschreitet die Größenbegrenzung.';
$string['errorbundlenestedziplimit'] = 'Die enthaltenen ZIP-Dateien überschreiten zusammen die Größenbegrenzung des temporären Bereichs von {$a}.';
$string['errorbundlefilelist'] = 'Die Plugin-ZIPs im Bundle-Stamm entsprechen nicht exakt den in bundle.json aufgeführten Dateien.';
$string['errorbundleextract'] = 'Die Plugin-ZIPs konnten nicht sicher aus dem Bundle entpackt werden.';
$string['errorbundlehashmismatch'] = 'Die SHA-256-Prüfsumme stimmt nicht mit dem enthaltenen ZIP überein: {$a}';
$string['errorbundlecomponentmismatch'] = 'Das Manifest erwartet {$a->expected} für {$a->file}, das Paket deklariert jedoch {$a->actual}.';
$string['errorbundlemanifest'] = 'Ungültige bundle.json: {$a}';
$string['errorbundlemanifestsize'] = 'Das Manifest darf nicht leer und höchstens {$a} groß sein.';
$string['errorbundleinvalidjson'] = 'Das Manifest ist kein gültiges JSON.';
$string['errorbundleobjectrequired'] = 'Der oberste JSON-Wert muss ein Objekt sein.';
$string['errorbundleunknownproperty'] = 'Unbekannte Eigenschaft(en) auf oberster Ebene: {$a}';
$string['errorbundlemissingproperty'] = 'Erforderliche Eigenschaft auf oberster Ebene fehlt: {$a}';
$string['errorbundleformat'] = 'format muss {$a} entsprechen.';
$string['errorbundleformatversion'] = 'Unterstützt werden formatversion 1 sowie die signierten formatversionen 2 und 3.';
$string['errorbundleactivation'] = 'Der signierte Aktivierungsabschnitt ist ungültig.';
$string['errorbundleactivationcomponent'] = 'Die Aktivierungsmetadaten verweisen auf eine Komponente, die nicht eindeutig im Bundle enthalten ist: {$a}';
$string['errorbundlelmhmissing'] = 'Dieses Bundle benötigt den 108design Plugin Manager, er ist aber weder installiert noch enthalten.';
$string['errorbundlelmhversion'] = 'Dieses Bundle benötigt Plugin-Manager-Version {$a->required}; verfügbar ist {$a->available}.';
$string['continuebundleactivation'] = 'Bundle-Aktivierung fortsetzen';
$string['errorbundleid'] = 'id muss ^[a-z][a-z0-9_-]{1,99}$ entsprechen.';
$string['errorbundletextproperty'] = 'Das Textfeld {$a} ist ungültig oder zu lang.';
$string['errorbundleplugincount'] = 'plugins muss zwischen 1 und {$a} Einträge enthalten.';
$string['errorbundlepluginobject'] = 'Plugin-Eintrag {$a} muss ein JSON-Objekt sein.';
$string['errorbundlepluginproperties'] = 'Plugin-Eintrag {$a} muss exakt file, component und sha256 enthalten.';
$string['errorbundlepluginfile'] = 'Plugin-Eintrag {$a} besitzt einen ungültigen ZIP-Dateinamen für den Stamm.';
$string['errorbundleplugincomponent'] = 'Plugin-Eintrag {$a} besitzt eine ungültige Moodle-Komponente.';
$string['errorbundlepluginhash'] = 'Plugin-Eintrag {$a} besitzt eine ungültige SHA-256-Prüfsumme.';
$string['errorbundleduplicatefile'] = 'Die Datei {$a} kommt im Manifest mehrfach vor.';
$string['errorbundleduplicatecomponent'] = 'Die Komponente {$a} kommt im Manifest mehrfach vor.';
$string['errorinvalidzip'] = 'Die Datei ist kein lesbares ZIP-Paket.';
$string['errortoomanyentries'] = 'Das ZIP enthält mehr als {$a} Einträge.';
$string['errorunsafepath'] = 'Das ZIP enthält einen unsicheren Pfad: {$a}';
$string['errorinvalidlayout'] = 'Das ZIP besitzt eine ungültige Paketstruktur.';
$string['errormultipleroots'] = 'Das ZIP muss genau ein Stammverzeichnis enthalten.';
$string['errorpackageuncompressedlimit'] = 'Das Paket überschreitet die entpackte Größenbegrenzung von {$a}.';
$string['errorbatchuncompressedlimit'] = 'Die Gruppe überschreitet die entpackte Größenbegrenzung von {$a}.';
$string['errorinvalidroot'] = 'Der Name des ZIP-Stammverzeichnisses ist ungültig: {$a}';
$string['errormissingversionphp'] = 'Direkt im Plugin-Stammverzeichnis wurde keine version.php gefunden.';
$string['errorextractmetadata'] = 'Die Paketmetadaten konnten nicht sicher extrahiert werden.';
$string['errormissingcomponent'] = 'version.php deklariert $plugin->component nicht.';
$string['errorinvalidcomponent'] = 'Ungültige Plugin-Komponente: {$a}';
$string['errorunknownplugintype'] = 'Der Plugin-Typ „{$a}“ ist dieser Website nicht bekannt. Möglicherweise muss zuerst ein neues übergeordnetes Plugin installiert werden.';
$string['errorcomponentrootmismatch'] = 'Komponente {$a->component} stimmt nicht mit dem ZIP-Stammverzeichnis {$a->root} überein.';
$string['errormissingversion'] = 'version.php deklariert keine numerische $plugin->version.';
$string['errorrequiresmoodle'] = 'Das Paket benötigt Moodle-Build {$a} oder neuer.';
$string['errorunsupportedbranch'] = 'Moodle-Zweig {$a->branch} liegt außerhalb des unterstützten Paketbereichs {$a->minimum}–{$a->maximum}.';
$string['warningdynamicdependencies'] = 'Die Abhängigkeitsdeklaration ist dynamisch oder konnte nicht vollständig gelesen werden. Moodle führt nach der Code-Verteilung seine normale Abhängigkeitsprüfung durch.';
$string['errordowngrade'] = 'Downgrade blockiert. Installiert ist Version {$a}.';
$string['warningsameversion'] = 'Das Paket besitzt dieselbe Versionsnummer wie das installierte Plugin und ersetzt dessen Code.';
$string['errorcorevalidation'] = 'Die Paketprüfung durch Moodle-Core ist fehlgeschlagen.';
$string['errorduplicatecomponent'] = 'Die Komponente {$a} kommt in dieser Gruppe mehrfach vor.';
$string['errormissingdependency'] = 'Die erforderliche Abhängigkeit {$a} ist weder installiert noch in dieser Gruppe enthalten.';
$string['errordependencyversion'] = 'Abhängigkeit {$a->component} benötigt Version {$a->required}; aus der Quelle „{$a->source}“ ist Version {$a->available} verfügbar.';

$string['eventbundlevalidated'] = 'Plugin-Gruppe geprüft';
$string['eventbundleinstallationstarted'] = 'Installation einer Plugin-Gruppe gestartet';
$string['taskcleanupstaging'] = 'Temporäre Dateien der Plugin-Bulk-Installation bereinigen';
$string['taskcleanupresult'] = '{$a} abgelaufene temporäre Verzeichnisse der Bulk-Installation wurden entfernt.';
$string['privacy:metadata'] = 'Der Bulk Installer speichert keine Teilnehmerdaten. Upload-Metadaten, Bestätigungen und Zugriffsverweise auf temporäre Ergebnisprotokolle liegen nur in der nativen Administrator-Sitzung. Temporäre Ergebnisdateien enthalten Komponenten und Status, aber keine Benutzerkennung oder Plugin-Inhalte. Moodle verwaltet Sitzungen und administrative Audit-Ereignisse.';
$string['errorbundlepublisher'] = 'Die Herausgeberangaben des signierten Bundles sind ungültig.';
$string['errorbundlesignature'] = 'Die Signatur des vorbereiteten Bundles ist ungültig. Das Bundle wurde möglicherweise verändert oder stammt nicht von 108design.';
$string['warningbundlepublisherfilename'] = 'Das signierte 108design-Bundle „{$a}“ ist gültig, sein Dateiname entspricht aber nicht der bundle_-Konvention. Die Installation wird dadurch nicht blockiert; behalte möglichst einen ursprünglichen Namen wie bundle_kurswerkzeuge-1.0.0.zip bei.';

// Contextual administration help.
$string['adminhelp'] = 'Hilfe zur Verwaltung';
$string['adminhelpmore'] = 'Mehr dazu im Hilfepanel';
$string['adminhelptopics'] = 'Hilfethemen';
$string['adminhelp_overview'] = 'Plugin-Pakete vorbereiten';
$string['adminhelp_overview_article'] = '<p>Lade das gewünschte Plugin-Paket oder ein unterstütztes Bundle hoch und prüfe danach die enthaltenen Komponenten, Versionen und Voraussetzungen.</p><p>Die Prüfung allein installiert noch nichts. Erstelle vor dem Bestätigen eine geeignete Sicherung der Moodle-Installation und ihrer Datenbank. Behalte eigene Anpassungen und vorhandene Plugin-Versionen im Blick.</p>';
$string['adminhelp_bundles'] = 'Bundles und Lizenzen';
$string['adminhelp_bundles_article'] = '<p>Ein Bundle kann mehrere zusammengehörige Plugins enthalten. Prüfe die gesamte Auswahl und benötigte Abhängigkeiten, bevor Du fortfährst.</p><p>Eine erfolgreiche Installation aktiviert kostenpflichtige Funktionen nicht automatisch. Verwalte passende Aktivierungen anschließend im <strong>108design Plugin Manager</strong> oder auf der Lizenzseite des jeweiligen Produkts.</p>';

$string['reviewnotinstalled'] = 'Nicht installiert';

$string['reviewbuild'] = 'Moodle-Build';

$string['reviewreasonrequires'] = 'Dieses Paket benötigt Moodle-Build {$a->build} oder neuer.';

$string['reviewreasondowngrade'] = 'Das Paket ist älter als der installierte Build. Wähle ein neueres Paket.';

$string['reviewreasonsameversion'] = 'Der gleiche Build ist installiert. Der vorhandene Plugin-Code wird ersetzt.';

$string['reviewreasonsupported'] = 'Dieses Paket unterstützt laut Metadaten die Moodle-Zweige {$a->minimum}–{$a->maximum}. Wähle ein Paket für diese Moodle-Version.';

$string['reviewreasontargetexists'] = 'Das Zielverzeichnis ist bereits vorhanden.';

$string['reviewreasonmaturity'] = 'Das Paket ist als Entwicklungsstand gekennzeichnet. Prüfe die Reifeangabe in den Details.';

$string['reviewnomessages'] = 'Keine Fehler oder Hinweise in dieser Paketprüfung.';

$string['reviewanyversion'] = 'Beliebige Version';

$string['reviewerror'] = 'Fehler';

$string['reviewwarning'] = 'Hinweis';

$string['reviewinfo'] = 'Information';

$string['reviewstateerror'] = 'Blockiert';

$string['reviewstatewarning'] = 'Hinweise prüfen';

$string['reviewstateready'] = 'Geprüft · bereit';

$string['reviewmessagecounts'] = '{$a->errors} Fehler · {$a->warnings} Hinweise';

$string['reviewfilterall'] = 'Alle';

$string['reviewfiltererror'] = 'Blockiert';

$string['reviewfilterwarning'] = 'Nur Hinweise';

$string['reviewfilterready'] = 'Bereit';

$string['reviewgroupready'] = 'Die Gruppe ist zur Übergabe bereit';

$string['reviewgroupblocked'] = 'Diese Gruppe ist blockiert';

$string['reviewgroupreadybody'] = 'Es gibt keine blockierenden Fehler. Bestätige einmal für die gesamte Gruppe.';

$string['reviewgroupblockedbody'] = '{$a} Pakete haben Fehler. Behebe die Fehler oder wähle passende Pakete aus, bevor Du die Gruppe übergibst.';

$string['reviewwarningpackages'] = 'Pakete mit Hinweisen ohne Fehler: {$a}.';

$string['reviewsource'] = 'Quelle';

$string['reviewsourceupdates'] = '108design Plugin Manager';

$string['reviewsourcesbundle'] = 'Bundle-ZIP';

$string['reviewsourceupload'] = 'ZIP-Upload';

$string['reviewstepmanager'] = 'Im Plugin Manager ausgewählt';

$string['reviewstepselected'] = 'Pakete ausgewählt';

$string['reviewstepcheck'] = 'Gruppe prüfen';

$string['reviewstepmoodle'] = 'Moodle-Installation';

$string['reviewsteps'] = 'Installationsfortschritt';

$string['reviewbatchmeta'] = '{$a->count} Pakete · {$a->size} entpackt · Moodle {$a->moodle}';

$string['reviewfilters'] = 'Prüfergebnisse filtern';

$string['reviewidentity'] = 'Plugin · geplante Aktion';

$string['reviewinstalled'] = 'Installiert';

$string['reviewtarget'] = 'Im Paket';

$string['reviewresult'] = 'Prüfergebnis';

$string['reviewpackages'] = 'Erkannte Plugins';

$string['reviewdetails'] = 'Details';

$string['reviewto'] = 'zu';

$string['reviewfilecomponent'] = 'Datei und Komponente';

$string['reviewcompatibility'] = 'Kompatibilität und Ziel';

$string['reviewrequires'] = 'Moodle-Mindestbuild';

$string['reviewsupported'] = 'Unterstützte Moodle-Zweige';

$string['reviewtargetpath'] = 'Zielverzeichnis';

$string['reviewdependency'] = 'Benötigte Abhängigkeit';

$string['revieworiginals'] = 'Prüfdetails und Originalmeldungen';

$string['reviewcorechecked'] = 'Übergabe an Moodle.';

$string['reviewhandoff'] = 'Nach Deiner Bestätigung prüft Moodle die Pakete, spielt die Plugin-Dateien nacheinander ein und führt Dich durch den normalen Upgrade-Ablauf.';

$string['reviewbackup'] = 'Sichere Dateien und Datenbank vor dem Einspielen. Wird die Installation nach „Pakete an Moodle übergeben“ unterbrochen, können einzelne Plugins bereits aktualisiert sein. „Abbrechen“ verwirft hier nur die vorbereitete Gruppe.';

$string['review'] = 'Gruppe prüfen';

$string['review_help'] = 'Vergleiche die installierte Version mit dem Paket. Rote Ergebnisse blockieren die gesamte Gruppe; unter Details findest Du vollständige Meldungen und Voraussetzungen.';

$string['adminhelp_review'] = 'Versionen und Ergebnisse prüfen';

$string['adminhelp_review_article'] = '<p><strong>Installiert</strong> und <strong>Im Paket</strong> zeigen die lesbare Version über ihrem numerischen Moodle-Build. Fehlt eine verlässliche Versionsangabe, erscheint allein der Build. Moodle entscheidet anhand der Builds über Update, Ersetzen oder Downgrade.</p><p>Die Filter ändern nur die sichtbaren Zeilen. Rote Fehler sperren die gesamte Gruppe. Hinweise müssen geprüft werden; das Ersetzen desselben Builds überschreibt dessen Code. <strong>Details</strong> enthält Originalmeldungen, Abhängigkeiten, Ziel und SHA-256.</p><p>Pakete aus dem <strong>108design Plugin Manager</strong> behalten die ausgewählte signierte Zielversion. Kataloggültigkeit, Paket-Hashes, Komponenten, Builds und Installationsstand werden vor dem Einspielen erneut geprüft.</p><p><strong>Pakete an Moodle übergeben</strong> bestätigt die gesamte Gruppe. Moodle prüft die Pakete, spielt Verzeichnisse nacheinander ein und setzt mit seinen normalen Abhängigkeits- und Datenbankprüfungen fort. Sichere vorher Dateien und Datenbank: Dies ist keine gemeinsame atomare Transaktion.</p>';

$string['reviewgroupbatchblockedbody'] = 'Die gemeinsame Prüfung der Gruppe ist fehlgeschlagen. Behebe die folgenden Probleme, bevor Du fortfährst.';

$string['reviewerrorcount'] = 'Fehler: {$a}';

$string['reviewwarningcount'] = 'Hinweise: {$a}';

$string['selectionempty'] = 'In dieser Auswahl bleiben keine Pakete übrig.';

$string['selectionchanged'] = 'Die Auswahl oder ihre Prüfergebnisse haben sich geändert. Prüfe die Gruppe erneut.';

$string['selectionpreview'] = 'Reduzierte Auswahl prüfen';

$string['selectionpreviewintro'] = 'Diese Pakete werden ausgeschlossen. Dazu gehören auch Pakete, deren benötigte Abhängigkeiten danach fehlen würden. Prüfe darunter die verbleibende Auswahl.';

$string['selectionpartial'] = 'Reduzierte Auswahl: {$a} Paket(e) ausgeschlossen.';

$string['selectionexcluded'] = 'Ausgeschlossene Pakete';

$string['selectionbundlemanual'] = 'Damit installierst Du einen Teil des Bundles. Richte Lizenzen anschließend in den installierten Plugins oder im Plugin Manager ein.';

$string['selectionbundleactivation'] = 'Damit installierst Du einen Teil des Bundles. Die anschließende Einrichtung enthält nur Produkte, deren Komponenten vollständig in dieser Auswahl sind.';

$string['selectionconfirm'] = 'Reduzierte Auswahl übernehmen';

$string['selectionunavailable'] = 'Es bleibt keine installierbare Auswahl übrig. Kehre zur Gruppe zurück und prüfe die Fehler.';

$string['selectionexclude'] = 'Blockierende Pakete ausschließen';

$string['selectionpreviewready'] = 'Die verbleibende Auswahl ist geprüft';
$string['selectionpreviewreadybody'] = 'Übernimm die reduzierte Auswahl. Anschließend kannst Du die Pakete an Moodle übergeben.';

$string['adminhelp_review_article'] .= '<p><strong>Blockierende Pakete ausschließen</strong> zeigt eine reduzierte Auswahl als Vorschau, einschließlich weiterer Pakete mit dann fehlenden Abhängigkeiten. <strong>Reduzierte Auswahl übernehmen</strong> bestätigt die Änderung; prüfe die verbleibenden Pakete vor der Übergabe an Moodle. Ausgeschlossene Dateien bleiben bis zum Verwerfen oder Einspielen aufgelistet. Bei einem Teil-Bundle enthält die Einrichtung nur vollständige Produkte und benötigt einen passenden Plugin Manager. Andernfalls richtest Du Lizenzen separat ein.</p>';

// Bulk Uninstaller.
$string['uninstalltab'] = 'Uninstaller';
$string['uninstallheading'] = 'Plugins gemeinsam deinstallieren';
$string['uninstallintro'] = 'Wähle nachinstallierte Plugins aus und prüfe vor der Deinstallation die vollständige Auswahl und ihre Folgen.';
$string['uninstallsearch'] = 'Name oder Komponente';
$string['uninstalltype'] = 'Plugin-Typ';
$string['uninstallstate'] = 'Status';
$string['uninstallalltypes'] = 'Alle Plugin-Typen';
$string['uninstallallstates'] = 'Alle Status';
$string['uninstallinstalled'] = 'Installiert';
$string['uninstallmissing'] = 'Dateien fehlen';
$string['uninstallcandidate'] = 'Für die Auswahl verfügbar; Abhängigkeiten werden anschließend geprüft.';
$string['uninstallselectvisible'] = 'Alle gefilterten auswählen';
$string['uninstallclear'] = 'Auswahl aufheben';
$string['uninstallcount'] = '{selected} ausgewählt · davon {hidden} ausgeblendet';
$string['uninstallfilterhelp'] = 'Core-Plugins sind ausgeschlossen. Filter ändern nur die Anzeige; ausgeblendete Häkchen bleiben ausgewählt und erscheinen in der vollständigen Vorschau.';
$string['uninstallremovefiles'] = 'Plugin-Dateien ebenfalls entfernen, soweit dies sicher möglich ist';
$string['uninstallempty'] = 'Keine nachinstallierten Plugins vorhanden.';
$string['uninstallnomatches'] = 'Keine Plugins entsprechen diesen Filtern.';
$string['uninstallreview'] = 'Auswahl prüfen';
$string['uninstallready'] = 'Die Auswahl kann deinstalliert werden';
$string['uninstallblocked'] = 'Die Auswahl enthält Blocker';
$string['uninstallaffectedcount'] = 'Betroffene Plugins einschließlich zugehöriger Unterplugins: {$a}.';
$string['uninstallchild'] = 'Wird zusammen mit {$a} entfernt.';
$string['uninstallfilesabsent'] = 'Plugin-Dateien fehlen bereits.';
$string['uninstallfilesauto'] = 'Plugin-Dateien werden ebenfalls entfernt.';
$string['uninstallfilesmanual'] = 'Dateien müssen manuell entfernt werden. Danach stoppt der Lauf.';
$string['uninstallusageunknown'] = 'Nutzung nicht vollständig ermittelbar';
$string['uninstallactivities'] = '{$a} Aktivitätsinstanzen';
$string['uninstallblocks'] = '{$a} Blockinstanzen';
$string['uninstallusagelimit'] = 'Die Nutzungszahlen zeigen vorhandene Aktivitäts- und Blockinstanzen. Auch bei 0 können weitere Daten betroffen sein. Die Vorschau kann nicht alle Auswirkungen beliebiger Drittanbieter-Plugins ermitteln.';
$string['uninstallorder'] = 'Geplante Reihenfolge';
$string['uninstallconsequences'] = 'Deinstallation kann Einstellungen, Inhalte, Dateien und weitere Daten der angezeigten Plugins dauerhaft löschen. Unterplugins sind eingeschlossen. Halte eine vollständige Sicherung bereit. Der Lauf arbeitet nacheinander, stoppt bei Fehlern oder manueller Restarbeit und bietet kein automatisches Rückgängigmachen.';
$string['uninstallacknowledge'] = 'Ich bestätige die vollständige angezeigte Auswahl einschließlich Unterplugins und den möglichen dauerhaften Datenverlust.';
$string['uninstallconfirm'] = 'Auswahl endgültig deinstallieren ({$a})';
$string['uninstallexclude'] = 'Blockierte Plugins abwählen';
$string['uninstallexclusionheading'] = 'Vorschau der reduzierten Auswahl';
$string['uninstallexclusionintro'] = 'Diese Plugins würden aus der Auswahl entfallen. Erst nach Deiner Bestätigung wird die reduzierte Auswahl übernommen. Es wird noch nichts deinstalliert.';
$string['uninstallapplyexclusion'] = 'Reduzierte Auswahl übernehmen';
$string['uninstallexcluded'] = 'Abgewählte Plugins und Gründe';
$string['uninstallerrorselection'] = 'Wähle zwischen 1 und 50 Plugins aus.';
$string['uninstallerrorunknown'] = '{$a->component}: Plugin nicht zuverlässig ermittelbar.';
$string['uninstallerrorself'] = '{$a->component}: Der Bulk Installer kann sich hier nicht selbst deinstallieren.';
$string['uninstallerrorstandard'] = '{$a->component}: Core-Plugins und Komponenten mit ungeklärter Herkunft sind ausgeschlossen.';
$string['uninstallerrorpending'] = '{$a->component}: Installation oder Upgrade ist nicht abgeschlossen. Kläre diesen Zustand zuerst in der Pluginübersicht.';
$string['uninstallerrornative'] = '{$a->component}: Dieser Plugin-Typ kann in diesem Lauf nicht sicher deinstalliert werden. Prüfe die Einzeldeinstallation in Moodle.';
$string['uninstallerrorrequired'] = '{$a->component} wird von {$a->detail} benötigt. Dieses abhängige Plugin gehört nicht zur Auswahl.';
$string['uninstallerrorcycle'] = '{$a->component}: Die Abhängigkeiten erlauben keine sichere Deinstallationsreihenfolge.';
$string['uninstallerrorpath'] = '{$a->component}: Der Plugin-Pfad enthält eine Verknüpfung, liegt außerhalb der Installation oder kann nicht sicher geprüft werden.';
$string['uninstallpolicy'] = 'Die Serverkonfiguration sperrt diese Web-Deinstallation oder ein Upgrade läuft. Kläre diesen Zustand zuerst.';
$string['uninstallstale'] = 'Die Bestätigung ist abgelaufen, wurde bereits verwendet oder der geprüfte Zustand hat sich geändert. Prüfe die Auswahl erneut.';
$string['uninstallincomplete'] = 'Moodle hat die Deinstallation nicht vollständig abgeschlossen.';
$string['uninstallackrequired'] = 'Bestätige zuerst die angezeigte Auswahl und den möglichen Datenverlust.';
$string['uninstallreceiptfailed'] = 'Das Ergebnisprotokoll konnte nicht sicher gespeichert werden. Der Lauf wird nicht fortgesetzt.';
$string['uninstallfailed'] = 'Der Schritt ist fehlgeschlagen oder wurde nur teilweise ausgeführt. Prüfe den tatsächlichen Zustand und das Serverprotokoll, bevor Du fortfährst.';
$string['uninstallresultcomplete'] = 'Der bestätigte Lauf ist abgeschlossen.';
$string['uninstallresultstopped'] = 'Der Lauf wurde gestoppt. Bereits ausgeführte Schritte bleiben wirksam; weitere Plugins wurden nicht verarbeitet.';
$string['uninstallresultinterrupted'] = 'Der Lauf wurde unterbrochen. Prüfe den tatsächlichen Zustand des letzten Schritts. Es erfolgt keine automatische Wiederholung.';
$string['uninstallresultrunning'] = 'Der Lauf wurde begonnen, aber ein Abschluss ist nicht bestätigt. Prüfe vor weiteren Schritten den tatsächlichen Zustand.';
$string['uninstallstepdone'] = 'Deinstalliert; vorhandene Plugin-Dateien entfernt.';
$string['uninstallstepmanual'] = 'Deinstalliert; beim Lauf verblieben Plugin-Dateien zur manuellen Entfernung. Der Lauf wurde gestoppt.';
$string['uninstallstepfailed'] = 'Fehlgeschlagen oder teilweise ausgeführt. Prüfe die aufgeführten Komponenten, bevor Du neu beginnst.';
$string['uninstallmanualcomponents'] = 'Beim Lauf nicht entfernte Plugin-Dateien: {$a}';
$string['uninstallnotrun'] = 'Nicht mehr verarbeitet';
$string['uninstalluncertain'] = 'Zuletzt begonnener Schritt mit ungeklärtem Ergebnis: {$a}';
$string['uninstallfinishnotice'] = 'Die deinstallierten Plugins und ihre Dateien wurden entfernt. Öffne über „Moodle-Abschlussprüfung öffnen“ die abschließende Prüfung. Moodle prüft dort den geänderten Plugin-Bestand und zeigt Dir noch erforderliche Schritte. Schließe diese Prüfung ab, damit die Deinstallation vollständig abgeschlossen ist.';
$string['uninstallfinish'] = 'Moodle-Abschlussprüfung öffnen';
$string['uninstallback'] = 'Zur Plugin-Auswahl';
$string['uninstalllastresult'] = 'Ergebnis des letzten Laufs anzeigen';
$string['uninstallprogress'] = 'Deinstallationsfortschritt';
$string['bulkinstall:uninstall'] = 'Mehrere nachinstallierte Plugins nach ausdrücklicher Bestätigung deinstallieren';
$string['uninstall_help'] = 'Core und Bulk Installer selbst sind ausgeschlossen. Prüfe die vollständige Auswahl einschließlich Unterplugins und Datenverlust vor der Bestätigung.';
$string['uninstall'] = 'Deinstallation';
$string['uninstallfiles'] = 'Plugin-Dateien entfernen';
$string['uninstallfiles_help'] = 'Datenbank-Deinstallation und Dateientfernung sind getrennte Schritte. Nicht entfernbare oder extern verwaltete Plugin-Dateien erfordern manuelle Nacharbeit.';
$string['adminhelp_uninstall'] = 'Plugins gemeinsam deinstallieren';
$string['adminhelp_uninstall_article'] = '<p>Wähle nachinstallierte Plugins aus und öffne <strong>Auswahl prüfen</strong>. Core-Plugins sind ausgeschlossen; der Bulk Installer ist geschützt. Eine separate Deinstallationsberechtigung ist zusätzlich zur Website-Konfiguration erforderlich.</p><p>Filter ändern nur die Anzeige. Auch ausgeblendete Häkchen gehören zur vollständigen Vorschau. Zugehörige Unterplugins erscheinen ausdrücklich in der Liste. Nicht ausgewählte abhängige Plugins blockieren die Entfernung; sie werden niemals automatisch hinzugefügt.</p><p><strong>Blockierte Plugins abwählen</strong> zeigt eine reduzierte Auswahl mit allen entfallenden Plugins und Gründen. Erst <strong>Reduzierte Auswahl übernehmen</strong> übernimmt sie. Danach ist eine eigene endgültige Deinstallationsbestätigung erforderlich.</p><p>Prüfe Nutzungszahlen und zusätzliche Moodle-Hinweise. Die Zahlen erfassen Aktivitäts- und Blockinstanzen, aber nicht sämtliche Daten oder Auswirkungen. Auch ein deaktiviertes Plugin oder ein Wert von 0 bedeutet nicht, dass keine Daten verloren gehen.</p><p>Die Bestätigung gilt eine Stunde und wird nur einmal verwendet. Geänderte Dateien, Versionen, Abhängigkeiten oder erfasste Nutzung erfordern eine neue Prüfung. Bei Fehlern stoppt der Lauf; abgeschlossene Schritte werden nicht zurückgenommen. Abbrechen verwirft nur die Vorbereitung.</p>';
$string['adminhelp_uninstallfiles'] = 'Dateien und Abschluss';
$string['adminhelp_uninstallfiles_article'] = '<p>Moodle entfernt zunächst die installierten Plugin-Daten. Mit aktivierter Dateientfernung werden anschließend die geprüften Plugin-Verzeichnisse entfernt, soweit dies sicher möglich ist. Verknüpfungen und Pfade außerhalb dieser Moodle-Installation sind gesperrt.</p><p>Schreibgeschützte oder extern verwaltete Plugin-Dateien werden nicht automatisch gelöscht. Der Lauf stoppt und zeigt die betroffenen Komponenten. Bei ausgeschalteter Dateientfernung gilt dasselbe.</p><p>Im laufenden Abschluss werden tatsächlich verbliebene Verzeichnisse aufgelistet. Entferne diese auf dem Server und wähle anschließend „Dateien erneut prüfen“. Dieser Button löscht nichts und wiederholt keine Deinstallation. Erst nach erfolgreicher Prüfung steht „Moodle-Abschlussprüfung öffnen“ bereit. Moodle prüft dort den geänderten Plugin-Bestand und zeigt Dir weitere nötige Schritte. Verbliebener Code kann sonst eine erneute Installation auslösen. Ein Abbruchbutton wird nach der Ausführung nicht angeboten.</p><p>„Ergebnis des letzten Laufs anzeigen“ öffnet nur den historischen Beleg und enthält keine Abschlussaktionen. Der Beleg dokumentiert den Zustand beim Lauf; spätere Dateinachprüfungen verändern ihn nicht.</p><p>Halte vor der Ausführung eine vollständige Sicherung bereit. Der Uninstaller erstellt kein automatisches Backup und bietet kein automatisches Rückgängigmachen. Temporäre Belege enthalten Komponenten, geprüfte Plugin-Pfade und Status, bleiben höchstens 24 Stunden abrufbar und werden durch die vorhandene Bereinigungsaufgabe entfernt.</p><p>Bei einem Abbruch schreibt der Bulk Installer zusätzliche technische Diagnoseeinträge in das PHP- beziehungsweise Webserver-Fehlerprotokoll. Sie enthalten eine Anfragekennung, den Verarbeitungsschritt, die betroffene Komponente und technische Fehlerstellen. Diese BI-Einträge enthalten keine Fehlermeldungstexte, Formularinhalte, Bestätigungstoken oder Nutzerkennungen. Die Aufbewahrung der Serverprotokolle richtet sich nach Deiner Serverkonfiguration; die 24-Stunden-Frist gilt nur für Ergebnisbelege. Die Protokollierung wiederholt keine Deinstallation.</p>';

$string['eventuninstallbatchstarted'] = 'Plugin-Deinstallationslauf begonnen';
$string['eventuninstallbatchfinished'] = 'Plugin-Deinstallationslauf beendet';

$string['uninstallproposalready'] = 'Die reduzierte Auswahl ist bereit zur Übernahme';

$string['uninstallhistoryheading'] = 'Ergebnis des letzten Deinstallationslaufs';

$string['uninstallcompletionheading'] = 'Deinstallation abschließen';

$string['uninstallreceiptunavailable'] = 'Für Deine Sitzung ist kein Ergebnisbeleg verfügbar. Belege werden höchstens 24 Stunden aufbewahrt.';

$string['uninstallremainingfiles'] = 'Folgende Plugin-Verzeichnisse sind noch vorhanden:';

$string['uninstallmanualnotice'] = 'Entferne diese Plugin-Verzeichnisse auf dem Server. Verbliebener Code kann sonst eine erneute Installation auslösen. Prüfe anschließend über „Dateien erneut prüfen“, ob alle aufgeführten Verzeichnisse entfernt wurden. Der Button prüft die Dateien; er löscht sie nicht.';

$string['uninstallrecheck'] = 'Dateien erneut prüfen';

$string['uninstallcompletionuncertain'] = 'Der Abschluss lässt sich noch nicht sicher bestätigen. Prüfe die oben gemeldeten Fehler und den Plugin-Status, bevor Du fortfährst. Die Dateinachprüfung wiederholt keine Deinstallation und stellt keine Daten wieder her.';

$string['uninstallnextstep'] = 'Nächster Schritt';

// Installed publisher and edition protection.
$string['errorupdateguard'] = 'Das Update für {$a} wurde vom Herausgeber- oder Editionsschutz der installierten Ausgabe abgewiesen. Verwende ein freigegebenes Paket für die installierte Edition; die vorhandene Installation bleibt erhalten.';
$string['adminhelp_review_article'] .= '<p>Geschützte installierte Plugins prüfen zusätzlich die Herausgebersignatur und Edition ihres Ersatzpakets. Eine fehlgeschlagene Prüfung blockiert den gesamten Stapel. Verwende ein freigegebenes Paket für die installierte Edition. Der Bulk Installer prüft die genauen Paketdateien erneut, bevor er sie an Moodle übergibt.</p>';

$string['errorpendingcodedowngrade'] = 'Es sind bereits neuere Plugindateien (Build {$a}) vorhanden. Dieses ältere Paket kann sie nicht ersetzen.';

// Shared independent signed update protection.
$string['updateguard_heading'] = 'Update angehalten';
$string['updateguard_component'] = 'Das Paket für {$a} wurde abgewiesen.';
$string['updateguard_hint'] = 'Verwende ein freigegebenes Paket für die installierte Ausgabe. Du kannst den Vorgang erneut prüfen.';
$string['updateguard_unchanged'] = 'Dieser Vorgang hat keine Plugin-Dateien ersetzt.';
$string['updateguard_help'] = 'Das installierte Plugin prüft vor dem Ersetzen die Herausgebersignatur, den Paketinhalt, den erlaubten Updatekanal und die Kompatibilität. Eine gültige Signatur erlaubt keinen Wechsel auf eine andere Ausgabe. Wird ein Paket abgewiesen, stoppt die gesamte Sammelaktion.';
$string['updateguard_signature'] = 'Die Herausgebersignatur oder der Paketinhalt konnte nicht bestätigt werden.';
$string['updateguard_policy'] = 'Das Paket passt nicht zur installierten Ausgabe oder zum erlaubten Paketprofil.';
$string['updateguard_channel'] = 'Updates über diesen Kanal sind für dieses Plugin nicht freigegeben.';
$string['updateguard_metadata'] = 'Die Versions- oder Abhängigkeitsangaben sind nicht konsistent.';
$string['updateguard_dependency'] = 'Eine benötigte Plugin-Version fehlt. Aktualisiere die Abhängigkeit gemeinsam mit diesem Plugin oder vorher.';
$string['updateguard_approval'] = 'Die Pakete oder der installierte Stand haben sich seit der Vorschau geändert. Prüfe den Vorgang erneut.';
$string['updateguard_busy'] = 'Eine andere Installation läuft. Versuche es nach deren Abschluss erneut.';
$string['updateguard_installed'] = 'Der installierte Updateschutz ist unvollständig. Stelle die Dateien der zuletzt installierten freigegebenen Version wieder her.';
$string['updateguard_operation'] = 'Diese Kombination von Installationsaktionen wird nicht unterstützt. Starte einen einzelnen Updatevorgang erneut.';
$string['updateguard_failed'] = 'Das Update konnte nicht geprüft werden. Es wurde keine Installation gestartet.';
$string['updateguard_back'] = 'Zur Pluginübersicht';
// End shared independent signed update protection.

$string['errorupdateguardstale'] = 'In dieser Anfrage ist noch ein veralteter Updateschutz aktiv. Lade die Seite neu und prüfe den Stapel erneut. Bleibt die Meldung bestehen, leere die Moodle-Caches und lass den PHP-Opcodecache durch die Administration erneuern. Die Installation wurde nicht gestartet.';

// Explicit LE to Pro transition.
$string['errortransitionconsent'] = 'Bestätige den ausgewählten Editionswechsel ausdrücklich vor der Installation.';
$string['errortransitionlicence'] = 'Für diesen Wechsel fehlt die geprüfte, dieser Installation zugewiesene Freischaltung. Bereite die im jeweiligen Abschnitt genannte kostenlose Aktivierung oder Pro-Lizenz vor.';
$string['editionconsent'] = 'Ich stimme dem oben beschriebenen Editionswechsel und den angezeigten Nutzungsbedingungen zu.';
$string['editionconsent_help'] = 'Diese Zustimmung gilt für die angezeigte Installation, Ausgangsversionen, Zielpakete und gegebenenfalls die gewählte Kursaktivität. Ohne Zustimmung bleiben die LE-Ausgaben installiert. Eine kostenlose Aktivierung oder eine bezahlte Lizenz wird getrennt vorbereitet. Die jeweiligen Folgen für die Bearbeitung stehen in der Vorschau.';
$string['transitionconsequences'] = 'Deine Inhalte, Historien, Eigentümerschaft und Platzierungen bleiben erhalten. Script Manager benötigt Pro zum Bearbeiten und Ausführen von Skripten; die Ausführung bleibt nur innerhalb seiner signierten Ausfallfrist aktiv. Menu Builder benötigt Pro für die zentrale Bearbeitung; gewöhnliche gespeicherte Menüs werden auch nach Lizenzablauf weiter ausgegeben. Ein Rückwechsel durch Installation des LE-ZIPs mit niedrigerer Buildnummer ist ausgeschlossen. Bewahre ein Backup für eine vollständige Wiederherstellung der Installation auf. Dieser Installer löst keinen Kauf und keine Zahlung aus.';
$string['transitionready'] = 'Eine aktuelle Pro-Lizenz für diese Installation ist geprüft. Die Installation erfordert weiterhin Deine ausdrückliche Zustimmung.';
$string['transitionnotready'] = 'LE bleibt installiert. Bereite die Pro-Lizenz vor oder aktualisiere ihren Nachweis, bevor Du den Wechsel bestätigst. Testlizenz, Grace-Status, eine andere Installation oder eine fehlende signierte Lizenzherkunft reichen nicht aus.';
$string['transitionpreparelicence'] = 'Pro-Lizenz vorbereiten';
$string['transitionlicencekey'] = 'Vorhandener Pro-Lizenzschlüssel';
$string['transitionlicencekey_help'] = 'Gib einen Pro-Schlüssel ein, den Du bereits besitzt. Er wird zur bewussten Aktivierung auf dieser Installation an den eingerichteten Lizenzdienst übermittelt. Der Installer kauft keine Lizenz und speichert den Rohschlüssel nicht. Eine bereits gültig zugewiesene Pro-Lizenz benötigt keinen neuen Schlüssel.';
$string['transitionactivate'] = 'Vorhandene Lizenz aktivieren';
$string['transitionactivationfailed'] = 'Die Lizenz konnte für diesen Wechsel nicht geprüft werden. LE bleibt installiert. Prüfe Produkt, Zuweisung, Umgebung und aktuellen signierten Lizenzstatus und kehre danach zur Paketprüfung zurück.';
$string['transitionactivationintro'] = 'Aktivierung und Installation sind getrennte Entscheidungen. Das Aktivieren einer vorhandenen Lizenz kann ihren Installationsplatz belegen. Beim Abbruch der Installation bleibt LE installiert; die bewusst vorgenommene Aktivierung bleibt im Plugin Manager verwaltbar und kann dort freigegeben werden. Kehre anschließend zur Paketprüfung zurück. Die Zustimmung zur Installation ist weiterhin erforderlich.';
$string['transitionmanagerrequired'] = 'Installiere zuerst Plugin Manager 0.29 oder neuer und verbinde diese Installation. Wähle ihre Produktions- oder Entwicklungsumgebung bewusst aus. Der Lizenzdienst muss die signierte Lizenzherkunft unterstützen; ältere Nachweise benötigen eine Aktualisierung. Du kannst zurückkehren und abbrechen, ohne LE zu ersetzen.';
$string['transitionmanager'] = 'Plugin Manager öffnen';
$string['adminhelp_review_article'] .= '<p>Der ausdrückliche Wechsel von Script Manager oder Menu Builder LE zu Pro erfolgt nur hier. Prüfe zuerst die aktuelle Pro-Lizenz für diese Installation und bestätige danach die angezeigten Nutzungsfolgen und Zielpakete. Die Lizenzvorbereitung installiert keine Dateien. Beim Abbruch bleibt LE erhalten; eine zuvor bewusst aktivierte Lizenz bleibt im Plugin Manager verwaltbar. Nach Beginn des Dateiaustauschs führt Moodle das Datenbankupgrade fort. Bei einer Unterbrechung setzt Du es mit denselben geprüften Paketen und dem gespeicherten Wechselnachweis fort. Deinstalliere das Plugin dafür nicht. Ein Rückwechsel benötigt eine vollständige Wiederherstellung des Backups.</p>';
$string['transitionprerequisitesfirst'] = 'Aktualisiere BI und Plugin Manager zuerst in einem getrennten Schritt. Prüfe und bestätige danach den LE-Wechsel erneut.';

$string['frontpagechoicerequired'] = 'Wähle zuerst, ob die kostenlose Kursaktivität beibehalten oder zur bezahlten Ausgabe gewechselt werden soll.';
$string['frontpagechoicepackages'] = 'Für die bezahlte Kursaktivität müssen das reguläre mod_frontpage-Paket und eine gültige Lizenz vorliegen. Du kannst jederzeit „Kostenlose Kursaktivität beibehalten“ wählen. Fehlt das Paket, lade beide Zielpakete gemeinsam in einen neuen Stapel hoch.';
$string['frontpagecorefirst'] = 'Migriere zuerst den Frontpage Free Core oder wähle sein geprüftes Zielpaket im selben Stapel aus.';
$string['frontpagecoreconsequences'] = 'Der Frontpage Free Core bleibt kostenlos. Seiten, Revisionen, Dateien, Freigaben und der bisherige Startseitenschalter werden übernommen. Seine kostenlose Aktivierung über Dein verbundenes Konto wird vor dem Dateiaustausch vorbereitet. Für die Kursaktivität wählst Du den weiteren Weg gesondert.';
$string['frontpagepaidconsequences'] = 'Für den Wechsel zur kostenpflichtigen regulären Kursaktivität gelten die folgenden Bedingungen. Vor dem Austausch wird eine gültige, zu dieser Installation gehörende Lizenz geprüft. Bestehende Inhalte bleiben bei späterem Lizenzverlust darstellbar; ihre Bearbeitung benötigt die Freischaltung. Die niedrigere LE-Ausgabe kann anschließend nicht als Update zurückinstalliert werden. Es erfolgt kein Kauf durch diesen Installer.';
$string['frontpagechoiceintro'] = 'Kostenlose Kursaktivität beibehalten: Ihr Paket bleibt installiert und ihr bisheriger begrenzter Editor bleibt nutzbar, auch für neue Aktivitäten. Bezahlte Ausgabe: Das hochgeladene reguläre Modulpaket wird zusätzlich geprüft. Jede Änderung führt in diese Vorschau zurück; installiert wird erst nach Deiner abschließenden Zustimmung.';
$string['frontpagechoice_keep_le'] = 'Kostenlose Kursaktivität beibehalten';
$string['frontpagechoice_paid'] = 'Zur bezahlten Kursaktivität wechseln';
$string['frontpagechosen'] = 'Gewählt: {$a}';
$string['frontpagefreeprepare'] = 'Die kostenlose Core-Aktivierung nutzt Dein bereits verbundenes Konto und belegt die zugehörige Aktivierung. Sie löst keine Zahlung aus. Bei Abbruch der Installation bleibt eine bewusst vorbereitete Aktivierung im Plugin Manager sichtbar und kann dort verwaltet werden. Ohne Kontoverbindung kannst Du zurückgehen und die LE unverändert weiterverwenden.';
$string['frontpagefreeclaim'] = 'Kostenlosen Core für diese Installation aktivieren';

$string['frontpagefreeready'] = 'Die kostenlose Core-Aktivierung ist geprüft. Bestätige anschließend die gewählte Installation.';

$string['frontpagefreenotready'] = 'Die LE bleibt installiert. Bereite zuerst die kostenlose Core-Aktivierung vor. Dazu ist ein verbundenes Konto im Plugin Manager nötig; es entsteht keine Zahlung.';

$string['frontpagepolicyblocked'] = 'Die signierte Funktionsregel des gewählten Core-Ziels erlaubt den bezahlten Activity-Wechsel mit dieser Freischaltung nicht. Die installierten Ausgaben bleiben erhalten.';

$string['adminhelp_review_article'] .= '<p>Beim Wechsel von Frontpage aus dem Marketplace bleibt der Free Core kostenlos. Seine kostenlose Kontoaktivierung wird gesondert vorbereitet. Du kannst die kostenlose Kursaktivität mit dem bisherigen begrenzten Editor beibehalten oder ausdrücklich zur bezahlten regulären Ausgabe wechseln. Der bezahlte Wechsel verlangt Paket und geprüfte Lizenz vor dem Dateiaustausch. Auch bei späteren Core-Updates kannst Du ein zusätzlich angebotenes bezahltes Modulpaket aus der Auswahl nehmen. Jede Änderung führt in eine neue Vorschau; die abschließende Zustimmung bleibt erforderlich. Ohne Lizenz oder bei Abbruch bleibt die LE erhalten.</p>';

$string['transitionupgradecontinuation'] = 'Plane für den anschließenden Moodle-Datenbankupgrade eine Wartungsunterbrechung ein. Nach dem Dateiaustausch ist Abbrechen keine Rücknahme. Setze nach einer Unterbrechung denselben bestätigten Stand fort; deinstalliere das Plugin dafür nicht.';

$string['frontpagefreepreparetitle'] = 'Kostenlose Core-Aktivierung vorbereiten';

$string['storefrontcoreconsequences'] = 'Free Core erhält Deine Produkte, Kursangebote, Bestellungen und Verkaufshistorie. Der Moodle-Zahlungsweg bleibt enthalten. Bestehende Kurszugänge, Zahlungsnachweise und vorgemerkte Nachrichten bleiben erhalten. Die Migration löst keine Zahlung, Zugangsverlängerung oder Mail aus. Der kompatible kostenlose Einschreibebegleiter darf installiert bleiben. Weitere Lieferfunktionen werden nicht automatisch aktiviert.';
$string['storefrontcompanionconsequences'] = 'Der Einschreibebegleiter bleibt kostenlos. Vorhandene Zuordnungen, Rollen und gekaufte Zugänge bleiben erhalten. Er nutzt die Storefront-Free-Core-Aktivierung und benötigt keine eigene kostenpflichtige Lizenz. Bereite zuerst das ausgewählte Core-Paket vor oder nutze bei bereits installiertem Core den Plugin Manager. Beim alleinigen Core-Update darf der kompatible LE-Begleiter bleiben.';
$string['storefrontfreecompanion'] = 'Kostenloser Einschreibebegleiter';
$string['storefronttransitionblocked'] = 'Der Storefront-Wechsel ist gesperrt. Prüfe die bestätigte kostenlose Aktivierung und die Zielrichtlinie für den enthaltenen Moodle-Zahlungsweg. Installierter Plugincode bleibt erhalten, bis die gesamte ausgewählte Umstellung möglich ist.';
$string['storefronttransitioncorefirst'] = 'Wähle das kompatible Storefront-Free-Core-Paket mit aus oder aktualisiere zuerst den Core. Beim alleinigen Core-Update darf der vorhandene kompatible kostenlose Einschreibebegleiter bleiben.';
