<?php
namespace Padcmoi\VueBootstrap;

use Padcmoi\BundleApiSlim\Database;
use Padcmoi\BundleApiSlim\Misc;
use Padcmoi\BundleApiSlim\SanitizeData;
use PDO;

class VueBS4Table
{
    private static $conditionData = [
        'key' => null,
        'comparaison' => '=',
        'bind' => ':bind',
        'value' => '',
        'param' => 2,
    ], $addBeforeWhere = '';

    /**
     * Prepare les données pour $conditionData
     * @param {array} $additionalWhere
     *
     * @void
     */
    private static function setAdditionalWhere(array $additionalWhere = [])
    {
        foreach (array_keys($additionalWhere) as $key) {
            self::$conditionData[$key] = $additionalWhere[$key];
        }
    }

    /**
     * Ajoute une seule condition à la clause WHERE
     * qui sera suivi d'un AND puis le filter BS4
     *
     * @param {array} $bindValue
     * @param {boolean} type de requete preparé PDO
     *
     * @return {array} retourne le bindValue modifié ou non
     */
    private static function createConditionAdded(array $bindValue, $easy = false)
    {
        if (array_keys(self::$conditionData) == ['key', 'comparaison', 'bind', 'value', 'param'] && self::$conditionData['key']) {
            $obj = self::$conditionData;

            if ($easy) {
                array_unshift($bindValue, ['key' => $obj['bind'], 'value' => $obj['value'], 'param' => PDO::PARAM_STR]);
            } else {
                $bindValue[$obj['bind']] = $obj['value'];
            }

            self::$addBeforeWhere = $obj['key'] . $obj['comparaison'] . ' ' . $obj['bind'] . ' AND ';
        } else {
            self::$addBeforeWhere = '';
        }

        return $bindValue;
    }

    /**
     * Affiche les items et construit la requête SQL
     * @param {Object/null} $args
     * @param {Array} $options keys [ 'args','selector','extended_selector','add_before_where','orderBy','searchFilter','keyId' ]
     *
     * @return {Array}
     */
    public static function easyItems(string $table, array $options = [])
    {
        // Vérification des objets
        self::setAdditionalWhere(Misc::objectHasProperty($options, 'add_before_where', 'array') ? $options['add_before_where'] : []);
        $args = Misc::objectHasProperty($options, 'args', 'array') ? $options['args'] : null;
        $selector = Misc::objectHasProperty($options, 'selector', 'array') ? $options['selector'] : [];
        $extended_selector = Misc::objectHasProperty($options, 'extended_selector', 'string') ? $options['extended_selector'] : '';
        $orderBy = Misc::objectHasProperty($options, 'orderBy', 'array') ? $options['orderBy'] : [];
        $searchFilter = Misc::objectHasProperty($options, 'searchFilter', 'array') ? $options['searchFilter'] : [];
        $keyId = Misc::objectHasProperty($options, 'keyId', 'string') ? $options['keyId'] : 'filter';

        // construction des requetes SQL
        $limit = self::limit($args);
        $filter = self::clauseForFilter($searchFilter, $keyId);
        $extended_selector = $extended_selector != "" && count($selector) > 0 ? "," . $extended_selector : "";

        $bind_value = self::createConditionAdded([
            ['key' => ':filter', 'value' => self::filter(), 'param' => PDO::PARAM_STR],
            ['key' => ':currentPage', 'value' => $limit['limit'], 'param' => PDO::PARAM_INT],
            ['key' => ':perPage', 'value' => $limit['offset'], 'param' => PDO::PARAM_INT],
        ], true);

        $items = self::items(
            "SELECT DISTINCT " . implode(',', $selector) . $extended_selector . " " .
            "FROM " . $table . " " .
            "WHERE " . self::$addBeforeWhere . $filter .
            self::orderBy($orderBy) .
            "LIMIT :currentPage, :perPage ", $bind_value);

        return [
            'totalRows' => self::totalRows($table, $searchFilter, $keyId),
            'currentRows' => count($items),
            'items' => $items,
        ];
    }

    /**
     * Total insertions par rapport à la selection
     * @param {String} $table
     * @param {Array} $searchFilter
     * @param {String} $keyId
     *
     * @return {int}
     */
    private static function totalRows(string $table, array $searchFilter, string $keyId = 'filter')
    {
        $bind_value = self::createConditionAdded([
            ':filter' => self::filter(),
        ]);

        return Database::rowCount(
            "SELECT * " .
            "FROM " . $table . " " .
            "WHERE " . self::$addBeforeWhere . self::clauseForFilter($searchFilter, $keyId),
            $bind_value, ['fetch_named']
        );
    }

    /**
     * Retourne les items avec une requete SQL conçu pour Bootstrap Table
     * @param {string} req SQL
     * @param {array} req préparée
     *
     * @return {array}
     */
    public static function items(string $req, array $bindValue = [])
    {
        $sth = Database::request()->prepare($req);

        foreach ($bindValue as $obj) {
            foreach ($obj as $v) {
                if (!isset($obj['key']) || !isset($obj['value']) || !isset($obj['param'])) {
                    continue;
                }

                $sth->bindValue($obj['key'], $obj['value'], $obj['param']);
            }
        }

        $sth->execute();
        $sth->setFetchMode(PDO::FETCH_NAMED); // Uniquement des clés nommées

        $items = $sth->fetchAll();
        $sth->closeCursor();

        return $items;
    }

    /**
     * Construit une requete orderBy
     * @param {array} $allowedSortBy
     *
     * @return {string}
     */
    public static function orderBy(array $allowedSortBy = [])
    {
        $input = SanitizeData::clean();

        $sortBy = isset($input['sortBy']) ? $input['sortBy'] : '';
        $sortDesc = isset($input['sortDesc']) && Misc::convertStrToBool($input['sortDesc']) ? 'DESC' : 'ASC';

        // Concernant la sécurité & l'escape SQL !!! les variables $sortBy & $sortDesc sont sécurisées
        // si $sortBy est pas dans la liste de $allowedSortBy alors la requete ne sera pas construite
        // peu importe ce que recevra sortDesc, la valeur finale sera soit DESC soit ASC
        if (!in_array($sortBy, $allowedSortBy)) {
            return ' ';
        }

        return " ORDER BY `" . $sortBy . "` " . $sortDesc . " ";
    }

    /**
     * Retourne uniquement les valeurs pour la clause LIMIT
     * calcul le currentPage
     *
     * @return {array}
     */
    public static function limit($args = null, int $limitPerPage = 50)
    {
        if (gettype($args) === "array") {
            $perPage = isset($args['per_page']) ? intval($args['per_page']) : 5;
            $currentPage = isset($args['current_page']) ? intval($args['current_page'] - 1) : 0;
        } else {
            $input = SanitizeData::clean();
            $perPage = isset($input['perPage']) ? intval($input['perPage']) : 5;
            $currentPage = isset($input['currentPage']) ? intval($input['currentPage'] - 1) : 0;
        }

        if ($perPage >= $limitPerPage) {$perPage = $limitPerPage;}

        $currentPage = $currentPage * $perPage;

        return ['limit' => intval($currentPage), 'offset' => intval($perPage)];
    }

    /**
     * Pour une recherche, pour la clause WHERE
     *
     * @return {string}
     */
    public static function filter()
    {
        $input = SanitizeData::clean();
        return isset($input['filter']) ? '%' . $input['filter'] . '%' : '%%';
    }

    /**
     * Construit la requete search pour la clause WHERE
     * @param {array} les colonnes de recherche
     * @param {string}
     *
     * @return {string}
     */
    public static function clauseForFilter(array $searchs = [], string $keyId)
    {
        $str = array_map(function ($v) {
            $v = "LCASE(" . strtolower($v) . ")";
            return $v;
        }, $searchs);

        $str = implode(' LIKE :' . $keyId . ' OR ', $str);

        if (count($searchs) >= 1) {$str .= " LIKE :" . $keyId;}

        return " (" . $str . ") ";
    }
}