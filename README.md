# Component VueBootstrap for PHP frameworks 


# ➡️Install
```
composer require padcmoi/vue-bootstrap-utils-for-php-api
```


# ➡️Usage
Example Usage in a project
```PHP
use Padcmoi\VueBootstrap\VueBS4Table;

class Table
{
    protected static function items($args = null)
    {
        $SELECTOR = ["id", "created_at", "username"];
        $SEARCHS_FILTER = ["id", "created_at", "username"];

        $extended_selector = ""; // pour ajouter des fonctions SQL

        return VueBS4Table::easyItems('users', [
            'args' => $args,
            'selector' => $SELECTOR,
            'orderBy' => $SELECTOR,
            'searchFilter' => $SEARCHS_FILTER,
            'add_before_where' => [
                'key' => 'id',
                'comparaison' => '>=',
                'bind' => ':bind',
                'value' => '197',
            ]
        ]);
    }
}
```

Example Usage with 2 SQL Join in a project
```PHP
<?php
namespace App\Application\Manager\Desires;

use App\Application\Manager\Desires\Misc\DesireExtends;
use App\Application\Manager\ResponseManager;
use Padcmoi\VueBootstrap\VueBS4Table;

class DesiresList extends DesireExtends
{
    const SELECTOR = [
        "t1.id",
        "t1.created_at",
        "t2.nom",
        "t3.libelle",
        "t1.complement",
        "t1.date",
        "t1.moment",
        "t1.date_choisie",
        "t1.destinataire_proposition",
        "t1.etat",
    ],
    ORDER_BY = [
        "id",
        "created_at",
        "nom",
        "libelle",
        "complement",
        "date",
        "moment",
        "date_choisie",
        "destinataire_proposition",
        "etat",
    ],
    SEARCHS_FILTER = ["t1.id", "t2.nom", "t3.libelle", "t1.complement", "t1.date", "t1.date_choisie"];

    /**
     * Affiche les items
     * @param {array/NULL} provient de SLIM
     *
     * @void
     */
    public static function items($args = null)
    {
        VueBS4Table::distinct(true); // Ajoute dans la requete DISTINCT apres SELECT, par défaut désactivé
    
        ResponseManager::add(['desires_list' =>
            VueBS4Table::easyItems('envie AS t1 LEFT JOIN utilisateur AS t2 ON(t1.utilisateur=t2.id) LEFT JOIN activite AS t3 ON(t1.activite=t3.id)', [
                'args' => $args,
                'selector' => self::SELECTOR,
                'orderBy' => self::ORDER_BY,
                'searchFilter' => self::SEARCHS_FILTER,
            ]),
        ]);
    }
}
```

# ➡️Others
##### 🧳Packagist
https://packagist.org/packages/padcmoi/vue-bootstrap-utils-for-php-api

##### 🔖Licence
Ce travail est sous licence [MIT](/LICENSE).

##### 🔥Pour me contacter sur discord
Lien discord [discord.gg/257rUb9](https://discord.gg/257rUb9)

##### 🍺Si vous souhaitez m’offrir un pourboire
Me faire un don 😍 [par Paypal](https://www.paypal.com/paypalme/Julien06100?locale.x=fr_FR)
