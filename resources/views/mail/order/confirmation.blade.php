<x-mail::message>
  <div>
    Liebe/r Frau Müller Fan<br><br>
    @if ($template['key'] === 'geschenk-abo')
    Danke, dass du das Geschenkabo von Frau Müller bestellt hast.<br><br>
    Du hast das Abo mit Twint bezahlt und da gibt es leider keine Möglichkeit, die Adresse der beschenkten Person anzugeben. Wenn du eine andere Person mit dem Magazin beschenken möchtest, gib uns bitte die Adresse dieser Person an, damit wir das Magazin korrekt versenden können.<br><br>
    Die von dir beschenkte Person bekommt natürlich auch noch Socken. Die sind noch in der Produktion – sorry für die Verzögerung! Sie werden natürlich sobald wie möglich nachgeliefert.<br><br>
    Nun wünschen wir dir eine tolle EM – hoffentlich sehen wir uns vor, im oder neben dem Stadion!<br><br>
    @endif
    @if ($template['key'] === 'jahresabo-2026')
    Danke, dass du ein Jahresabo 2026 bestellt hast und willkommen im Fanclub von Frau Müller. Mit diesem Abo bekommst du das zweite und dritte Magazin von Frau Müller.<br><br>
    Nun wünschen wir dir eine tolle EM – hoffentlich sehen wir uns vor, im oder neben dem Stadion!<br><br>
    @endif
    @if ($template['key'] === 'erste-ausgabe')
    Danke, dass du die Erstausgabe bestellt hast und willkommen im Fanclub von Frau Müller. Du bekommst das Magazin in den nächsten Tagen per Post zugeschickt.<br><br>
    Nun wünschen wir dir eine tolle EM – hoffentlich sehen wir uns vor, im oder neben dem Stadion!<br><br>
    @endif
    @if ($template['key'] === 'jahresabo-erstausgabe')
    Danke, dass du das Jahresabo Erstausgabe von Frau Müller bestellt hast. Du bekommst das Magazin in den nächsten Tagen per Post zugeschickt.<br><br>
    Wenn du Fragen hast, kannst du dich jederzeit gern an uns wenden oder auf unserer Webseite über Frau Müller informieren:<br>
    www.fraumueller.ch<br><br>
    Nun wünschen wir dir eine tolle EM – hoffentlich sehen wir uns vor, im oder neben dem Stadion!<br><br>
    @endif
    @if ($template['key'] === 'trikot' || $template['key'] === 'trikot-xs-s' || $template['key'] === 'trikot-m-l')
    Danke, dass du das Original Frau Müller Trikot bestellt hast und willkommen im Fanclub von Frau Müller.<br><br> 
    Wie du bereits bei der Bestellung gesehen hast, wird das Trikot erst im September geliefert. Du musst dich leider noch etwas gedulden.<br><br>
    Wenn du ein Abo von Frau Müller lösen möchtest (oder dich sonst auf unserer Webseite umschauen willst), kannst du das hier tun:<br>
    www.fraumueller.ch<br><br>
    Wir wünschen dir trotzdem weiterhin eine tolle EM – hoffentlich sehen wir uns vor, im oder neben dem Stadion!<br><br>
    @endif
    Und vergiss nicht: Frau Müller ♥️ you<br><br>
    Beste Grüsse<br>Frau Müller
  </div>
</x-mail::message>
