# Contribuer au projet
L'application ToDoList encourage la collaboration.
Chacun peut se sentir libre de signaler un problème technique ou fonctionnel en ajoutant des <i>issues</i>.
Il est également possible de soumettre une <i>pull request</i>, afin de proposer une modification du code existant, ou un ajout de fonctionnalité.
Ce présent document décrit un tel processus, dans le but d'homogénéiser le code des collaborateurs, de sorte à ce que chacun puisse s'y retrouver.
## Normes à respecter
Les identations doivent être respéctées.<br>
Le code soumis doit respecter certaines des <i>PHP Standars Recommendations</i> (PSRs) :
- [PSR-1](https://www.php-fig.org/psr/psr-1/) et [PSR-2](https://www.php-fig.org/psr/psr-2/), à propos du style du code
- [PSR-4](https://www.php-fig.org/psr/psr-4/), à propos de la correspondance entre le <i>namespace</i>
et le chemin du fichier associé

Quant aux spécificités du <i>framework</i> Symfony, les bonnes pratiques sont à mettre en place autant que possible.<br/>
Cela inclue entre autres :
- L'injection de dépendance et l'accès aux services par <i>type-hint</i> est à privilégier.
- La créaton et l'utilisation de service privé est à privilégier.
- Une route doit être indiquée par l'annotation `@Route` issue du `SensioFrameworkExtraBundle`.
- Tous les <i>templates</i> Twig doivent se trouver dans le dossier `app/Resources/views`.
## Comment soumettre une pull request
