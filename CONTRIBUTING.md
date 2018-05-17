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

Quant aux spécificités du <i>framework</i> Symfony, les [bonnes pratiques](https://symfony.com/doc/current/best_practices/index.html) sont à mettre en place autant que possible.<br/>
Cela inclue entre autres :
- L'injection de dépendance et l'accès aux services par <i>type-hint</i> sont à privilégier.
- La créaton et l'utilisation de service privé sont à privilégier.
- L'usage des `ParamConverter` est à privilégier.
- Une route doit être indiquée par l'annotation `@Route` issue du `SensioFrameworkExtraBundle`.
- Tous les <i>templates</i> Twig doivent se trouver dans le dossier `app/Resources/views`.
Il est également intéressant de respecter la règle des "5-10-20", stipulant qu'un `Controller` doit définir 5 variables au maximum, contenir 10 actions au maximum, et inclure 20 lignes de code au maximum pour chaque action.
## Comment soumettre une <i>pull request</i>
Pour la soumission d'une <i>pull request</i>, nous utilisons ici le [protocole](https://gist.github.com/MarcDiethelm/7303312) suivant :
- [Créez](https://help.github.com/articles/creating-a-pull-request-from-a-fork/) une <i>fork</i> de ce projet sur votre Github
- Clonez la <i>fork</i> sur votre machine, en local (votre <i>repository</i> distant est `origin`).
- Ajoutez le <i>repository</i> d'origine (celui depuis lequel est écrit le présent document) en tant que distant, et nommez-le `upstream`.
- Créez une nouvelle branche depuis la branche `develop` elle existe, sinon depuis la branche `master`.
- Apportez la modification voulue en respectant les normes de code associée à ce projet.
- Lancez les tests existant.
- Adaptez ou écrivez les tests, testant la modification apportée, et lancez-les.
- Complétez la documentation du projet si une nouvelle fonctionnalité a été implémentée.
- [Compressez](https://help.github.com/articles/about-git-rebase/) vos <i>commits</i> en un seul.
- Poussez votre branche sur votre <i>repository</i> `origin`.
- Depuis votre <i>fork</i>, ouvez une <i>pull request</i>. Ciblez la branche `develop` de ce projet si elle existe, sinon la branche `master`. Décrivez au mieux la modification apportée dans un commentaire associé à la <i>pull request</i>.
- Une fois la <i>pull request</i> approuvée et fusionnée, vous pouvez obtenir le projet en clonant le <i>repository</i> distant `upstream`, précédemment ajouté.
