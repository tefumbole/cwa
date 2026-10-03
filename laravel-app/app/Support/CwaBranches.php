<?php

namespace App\Support;

class CwaBranches
{
    /**
     * Cameroon CWA dioceses grouped by ecclesiastical province.
     *
     * @return array<int, array{key:string,title:string,seat:string,dioceses:array<int,string>}>
     */
    public static function cameroonProvinces()
    {
        return [
            [
                'key' => 'bamenda',
                'title' => 'Our Lady of Divine Grace Ecclesiastical Province of Bamenda',
                'seat' => 'Bamenda',
                'dioceses' => [
                    'Our Lady of Lourdes, Bamenda',
                    'Immaculate Heart of Mary, Kumba',
                    'Our Lady of Guadalupe, Buea',
                    'Our Lady of the Immaculate Conception, Kumbo',
                    'Gate of Heaven, Mamfe',
                ],
            ],
            [
                'key' => 'yaounde',
                'title' => 'Our Lady Queen of Heaven Ecclesiastical Province of Yaoundé',
                'seat' => 'Yaoundé',
                'dioceses' => [
                    'Our Lady Tower of Ivory, Yaoundé',
                    'Marie Reine de La Paix, Bafia',
                    'Notre Dame Reine de L’Univers, Kribi',
                    'Notre Dame Porte de Ciel et Sante des Malades, Mbalmayo',
                    'Notre Dame Reine de la Paix, Ebolowa',
                    'Mother of Good Counsel, Sangmelima',
                    'Our Lady Help of Christians, Obala',
                ],
            ],
            [
                'key' => 'douala',
                'title' => 'Our Lady of Perpetual Help Ecclesiastical Province of Douala',
                'seat' => 'Douala',
                'dioceses' => [
                    'Our Lady of Hope, Douala',
                    'Notre Dame de la Paix, Bafoussam',
                    'Coeur Immacule de Marie, Bafang',
                    'Immacule Conception, Nkongsamba',
                    'Notre Dame de Fatima, Edea',
                    'Notre Dame du Rosaire, Eseka',
                ],
            ],
            [
                'key' => 'garoua',
                'title' => 'Notre Dame de la Misericorde Divine Ecclesiastical Province of Garoua',
                'seat' => 'Garoua',
                'dioceses' => [
                    'Notre Dame de la Divine Grace, Yagoua',
                    'Notre Dame de Fatima, Maroua-Mokolo',
                    'Notre Dame du Mont Carmel, Ngoundere',
                    'Notre Dame de la Misericorde, Garoua',
                ],
            ],
            [
                'key' => 'bertoua',
                'title' => 'Ecclesiastical Province of Bertoua',
                'seat' => 'Bertoua',
                'dioceses' => [
                    'Etoile Du Matin, Batouri',
                    'Marie Reine de la Paix, Doume-Abong Mbang',
                    'Marie Mere de l’eglise, Bertoua',
                    'Our Lady Queen of Peace, Yokadouma',
                ],
            ],
        ];
    }

    /**
     * Diaspora countries / zones and their branches.
     *
     * @return array<int, array{key:string,title:string,countries:array<int,string>,zones:array<int, array{title:?string,branches:array<int,string>}>}>
     */
    public static function diaspora()
    {
        return [
            [
                'key' => 'north_america',
                'title' => 'North America',
                'countries' => ['USA/Canada', 'United States', 'USA', 'US'],
                'zones' => [
                    [
                        'title' => 'Queen Assumed into Heaven Zone',
                        'branches' => [
                            'Queen of Apostles',
                            'Queen of Angels',
                            'Queen of All Saints',
                        ],
                    ],
                    [
                        'title' => 'Mary Queen of Heaven Zone',
                        'branches' => [
                            'Our Lady of Good Counsel',
                            'Our Lady of Lourdes',
                            'Mother of Divine Grace',
                            'Our Lady of Hope',
                        ],
                    ],
                    [
                        'title' => 'Virgin Most Powerful',
                        'branches' => [
                            'Our Lady of Guadalupe',
                            'Tower of Ivory',
                            'Spiritual Vessel',
                        ],
                    ],
                    [
                        'title' => 'Our Lady of the Pillar Zone',
                        'branches' => [
                            'Our Lady Cause of our Joy',
                            'Queen of Angels',
                            'Gate of Heaven',
                        ],
                    ],
                    [
                        'title' => 'Our Lady Queen of Peace',
                        'branches' => [
                            'Our Lady of the Holy Rosary',
                            'Queen of All Saints',
                            'Our Lady of Perpetual Help',
                            'Mother most Admirable',
                        ],
                    ],
                    [
                        'title' => 'Mirror of Justice Zone',
                        'branches' => [
                            'Mother of our Saviour',
                            'Mary Mother of the Church',
                            'Our Lady Ark of the Covenant',
                            'Immaculate Heart of Mary',
                            'Our Lady of Fatima',
                            'Our Lady Queen of Peace',
                            'Our Lady of Perpetual Help',
                            'Vessel of Honor',
                            'Our Lady Queen of Peace',
                            'Our Lady Solace of Migrants',
                            'Morning Star',
                            'Our Lady of Fatima',
                            'Our Lady Queen of Peace',
                            'Our Lady of Mercy',
                            'Queen of peace Dallas',
                            'Our Lady of Consolation',
                            'Mystical Rose',
                            'Queen of Families Phoenix',
                            'Our Lady Queen of Peace',
                            'Mary Mother of Christ',
                            'Our Lady of Guadalupe',
                            'Immaculate Conception',
                            'Our Lady Seat of Wisdom',
                        ],
                    ],
                ],
            ],
            [
                'key' => 'canada',
                'title' => 'Canada',
                'countries' => ['Canada'],
                'zones' => [
                    [
                        'title' => null,
                        'branches' => [
                            'Our Lady Seat of Wisdom Branch, Calgary',
                            'Immaculate Conception, Chateaquay',
                        ],
                    ],
                ],
            ],
            [
                'key' => 'united_kingdom',
                'title' => 'United Kingdom',
                'countries' => ['United Kingdom', 'UK'],
                'zones' => [
                    [
                        'title' => null,
                        'branches' => [
                            'Our Lady Queen of Peace, Birmingham',
                            'Mother of Good Counsel Coventry',
                            'Our Lady Queen of Peace Portsmouth',
                            'Our Lady of the Rosary, Bristol',
                            'Our Lady Queen of Peace, Derby',
                            'Mirror of Justice, Leicester',
                            'Our Lady of Assumption, London',
                            'Immaculate heart of Mary, London',
                            'Our Lady of Victory, Kensington London',
                            'Our Lady of Rosary, North London',
                            'Queen of Peace, Glasgow',
                        ],
                    ],
                ],
            ],
            [
                'key' => 'belgium',
                'title' => 'Belgium',
                'countries' => ['Belgium'],
                'zones' => [
                    [
                        'title' => null,
                        'branches' => [
                            'Our Lady of Perpetual Help Antwerpen/Mechelen Branch',
                            'Our Lady Mother of Hope Gent Diocese',
                        ],
                    ],
                ],
            ],
            [
                'key' => 'germany',
                'title' => 'Germany',
                'countries' => ['Germany'],
                'zones' => [
                    [
                        'title' => null,
                        'branches' => [
                            'Our Lady of Rosary Branch, Wuppertal',
                            'Queen of Peace, Mulheim',
                        ],
                    ],
                ],
            ],
            [
                'key' => 'finland',
                'title' => 'Finland',
                'countries' => ['Finland'],
                'zones' => [
                    [
                        'title' => null,
                        'branches' => [
                            'Seat of Wisdom',
                        ],
                    ],
                ],
            ],
            [
                'key' => 'south_africa',
                'title' => 'South Africa',
                'countries' => ['South Africa'],
                'zones' => [
                    [
                        'title' => null,
                        'branches' => [
                            'Our Lady of Perpetual Help',
                        ],
                    ],
                ],
            ],
            [
                'key' => 'norway',
                'title' => 'Norway',
                'countries' => ['Norway'],
                'zones' => [
                    [
                        'title' => null,
                        'branches' => [
                            'Our Lady Queen of Peace, Lillestrom Norway',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function cameroonDioceses()
    {
        $out = [];
        foreach (self::cameroonProvinces() as $province) {
            foreach ($province['dioceses'] as $name) {
                if (! in_array($name, $out, true)) {
                    $out[] = $name;
                }
            }
        }

        return $out;
    }

    /**
     * Official diocese / branch names for a membership country.
     * USA/Canada includes North America and Canada lists.
     *
     * @return array<int, string>
     */
    public static function diocesesForCountry($country)
    {
        $country = trim((string) $country);
        if ($country === '' || self::sameCountry($country, 'Cameroon') || self::sameCountry($country, 'Cameroun')) {
            return self::cameroonDioceses();
        }

        $out = [];
        foreach (self::diaspora() as $group) {
            $include = self::groupMatchesCountry($group, $country)
                || (self::isUsaCanada($country) && in_array($group['key'], ['north_america', 'canada'], true));
            if (! $include) {
                continue;
            }
            foreach (self::flattenGroup($group) as $name) {
                if (! in_array($name, $out, true)) {
                    $out[] = $name;
                }
            }
        }

        return $out;
    }

    public static function hasMappedList($country)
    {
        return count(self::diocesesForCountry($country)) > 0;
    }

    public static function acceptsDiocese($country, $name)
    {
        $name = trim((string) $name);
        if ($name === '') {
            return false;
        }
        $list = self::diocesesForCountry($country);
        if ($list === []) {
            return true;
        }

        return in_array($name, $list, true);
    }

    /**
     * Country => diocese/branch names for the membership wizard.
     *
     * @return array<string, array<int, string>>
     */
    public static function formMap()
    {
        $map = [
            'Cameroon' => self::cameroonDioceses(),
            'Cameroun' => self::cameroonDioceses(),
        ];
        foreach (self::diaspora() as $group) {
            $names = array_values(array_unique(self::flattenGroup($group)));
            foreach ($group['countries'] as $country) {
                $map[$country] = $names;
            }
        }
        $map['USA/Canada'] = array_values(array_unique(array_merge(
            $map['USA/Canada'] ?? [],
            $map['Canada'] ?? []
        )));

        return $map;
    }

    /**
     * @param  array{zones:array<int, array{branches:array<int,string>}>}  $group
     * @return array<int, string>
     */
    protected static function flattenGroup(array $group)
    {
        $out = [];
        foreach ($group['zones'] as $zone) {
            foreach ($zone['branches'] as $name) {
                $out[] = $name;
            }
        }

        return $out;
    }

    protected static function groupMatchesCountry(array $group, $country)
    {
        foreach ($group['countries'] as $alias) {
            if (self::sameCountry($country, $alias)) {
                return true;
            }
        }

        return false;
    }

    protected static function isUsaCanada($country)
    {
        return self::sameCountry($country, 'USA/Canada')
            || self::sameCountry($country, 'United States')
            || self::sameCountry($country, 'USA')
            || self::sameCountry($country, 'US');
    }

    protected static function sameCountry($a, $b)
    {
        return strcasecmp(trim((string) $a), trim((string) $b)) === 0;
    }
}
