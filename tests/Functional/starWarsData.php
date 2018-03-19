<?php

namespace Digia\GraphQL\Test\Functional;

function luke()
{
    return [
        'type'       => 'Human',
        'id'         => '1000',
        'name'       => 'Luke Skywalker',
        'friends'    => ['1002', '1003', '2000', '2001'],
        'appearsIn'  => ['NEWHOPE', 'EMPIRE', 'JEDI'],
        'homePlanet' => 'Tatooine',
    ];
}

function vader()
{
    return [
        'type'       => 'Human',
        'id'         => '1001',
        'name'       => 'Darth Vader',
        'friends'    => ['1004'],
        'appearsIn'  => ['NEWHOPE', 'EMPIRE', 'JEDI'],
        'homePlanet' => 'Tatooine',
    ];
}

function han()
{
    return [
        'type'      => 'Human',
        'id'        => '1002',
        'name'      => 'Han Solo',
        'friends'   => ['1000', '1003', '2001'],
        'appearsIn' => ['NEWHOPE', 'EMPIRE', 'JEDI'],
    ];
}

function leia()
{
    return [
        'type'       => 'Human',
        'id'         => '1003',
        'name'       => 'Leia Organa',
        'friends'    => ['1000', '1002', '2000', '2001'],
        'appearsIn'  => ['NEWHOPE', 'EMPIRE', 'JEDI'],
        'homePlanet' => 'Alderaan',
    ];
}

function tarkin()
{
    return [
        'type'      => 'Human',
        'id'        => '1004',
        'name'      => 'Wilhuff Tarkin',
        'friends'   => ['1001'],
        'appearsIn' => ['NEWHOPE'],
    ];
}

function humanData()
{
    return [
        '1000' => luke(),
        '1001' => vader(),
        '1002' => han(),
        '1003' => leia(),
        '1004' => tarkin(),
    ];
}

function threepio()
{
    return [
        'type'            => 'Droid',
        'id'              => '2000',
        'name'            => 'C-3PO',
        'friends'         => ['1000', '1002', '1003', '2001'],
        'appearsIn'       => [4, 5, 6],
        'primaryFunction' => 'Protocol',
    ];
}

function artoo()
{
    return [
        'type'            => 'Droid',
        'id'              => '2001',
        'name'            => 'R2-D2',
        'friends'         => ['1000', '1002', '1003'],
        'appearsIn'       => [4, 5, 6],
        'primaryFunction' => 'Astromech',
    ];
}

function droidData()
{
    return [
        '2000' => threepio(),
        '2001' => artoo(),
    ];
}

function getCharacter($id)
{
    return getHuman($id) ?? getDroid($id) ?? null;
}

function getFriends($character)
{
    return array_map(function ($id) {
        return getCharacter($id);
    }, $character['friends']);
}

function getHero($episode)
{
    if ($episode === 'EMPIRE') {
        return luke();
    }

    return artoo();
}

function getHuman($id)
{
    return humanData()[$id] ?? null;
}

function getDroid($id)
{
    return droidData()[$id] ?? null;
}
