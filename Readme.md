# Gregoriophp

Ce dépôt est un *fork* de [gregoriophp](https://code.google.com/p/gregoriophp/),
adapté pour que la compilation des PDF se fasse en local, sans dépendre du
serveur de http://illuminarepublications.com/.

## Installation

### Dépendances

* apache2 avec support de php5
* unzip
* [gregorio, gregoriotex](http://home.gna.org/gregorio/) et leurs dépendances

### Installation

Les titres décrivent les étapes en général : le détail des commandes vaut
sous Ubuntu, à vous de l'adapter à vos besoins. En particuler, si vous changez
le nom du répertoire sur le serveur, pensez à adapter ce qui doit l'être dans
*index.html* et *process.php*.

#### Clonage du dépôt sur le serveur

En tant que superutilisateur, saisissez les commandes suivantes :

    cd /var/www/
    git clone https://github.com/jperon/gregoriophp.git
    cd gregoriophp/
    chown -R www-data:www-data .
    chmod -R 755 .

#### Copie des polices de caractères dans le dossier de polices du système

Toujours en tant que superutilisateur :

    mkdir /usr/share/fonts/gregoriophp/
    cp ./fonts/* /usr/share/fonts/gregoriophp/
    cd /usr/share/fonts/gregoriophp/
    for f in *.zip ; do unzip $f ; done

#### Accès au site à-travers le navigateur

Dans votre navigateur (chrome ou chromium recommandé, mais firefox fonctionne),
saisissez l'adresse :
[http://localhost/gregoriophp/](http://localhost/gregoriophp/)

Adaptez les paramètres à vos besoins, saisissez votre partition (pourquoi pas
par un copier-coller depuis [gregobase](http://gregobase.selapa.net/))
en veillant à ne pas supprimer ce qui précède la ligne %%, puis
lancez l'export en PDF.

Si ni vous ni moi n'avons rien oublié, cela devrait fonctionner. Si vous
êtes certain d'avoir suivi scrupuleusement les étapes et que cela ne fonctionne
pas, n'hésitez pas à me le signaler. Si vous avez besoin d'aide pour l'usage
du gabc, voyez [le site de gregorio](http://home.gna.org/gregorio/),
qui propose une *mailing-list*.
