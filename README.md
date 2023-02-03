# character_count
Sehr einfaches Plugin um Zeichen zu zählen.
Verschiedene einstellungsmöglichkeiten. 

evt. Anpassungen der Bezeichnungen, bzw. der Ausgabe im Javascript vornehmen
(jscripts/character_count.js)
    
Anpassung für die Klasse, wenn mindestpostinglänge nicht erreicht ist im character_count.css  
Template bei den globalen:  
- count_characters_counter    

# Einstellungen:
    
Foren
In welchen Foren soll der Zeichenzähler angezeigt werden - Elternforen reichen.   
 - Alle Foren   
 - Wähle Foren    
 - keine    
    
Soll eine Mindestpostinglänge angezeigt werden?   
Wenn ja:    
Mindestzeichenanzahl    
    
    
Zählart   
- Mit Leerzeichen   
- Wörter    
- Ohne Leerzeichen    
    
      
Html (mitzählen oder nicht)  
    
    
# Templates - eingefügte variablen:   

newreply, editpost, newthread, showthread_quickreply':
  - {$charactercounter}   
  - <script type="text/javascript" src="{$mybb->asset_url}/jscripts/count_characters.js"></script>    
