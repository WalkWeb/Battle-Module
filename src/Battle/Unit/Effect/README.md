
# Эффекты

Эффекты реализованы максимально гибко, и их концепция может потребовать дополнительного объяснения.

Ключевой архитектурный подход Battle Module — это взаимодействие на юнита через абстрактные Action. При этом с внешней
точки зрения не важно, что это (удар, лечение, бафф и т.д.) — просто передаем Action юниту, и говорим примени его к 
себе.

Это в свою очередь позволяет создать очень гибкие эффекты, которые при трех событиях:

- Добавление эффекта юниту
- Наступление нового раунда
- Удаление эффекта

Возвращают ActionCollection, которые будут применяться к юниту. При этом, как уже говорилось ранее, это может быть что 
угодно — урон, лечение, баф и даже другой эффект.

Все это в сумме позволяет создавать эффекты, которые, например, будут:

- При добавлении эффекта накладывать на него баф
- На каждом раунде восстанавливать немного здоровья
- При удалении накладывать другой эффект с дебафом

А можно создавать эффекты с постепенно нарастающим эффектом (положительным, или отрицательным — неважно), т.е.:

- При добавлении эффекта накладывается дебаф
- При удалении эффекта накладывается другой эффект, с еще более сильной версией этого же дебафа. Который, при своем 
удалении, в свою очередь, добавит еще более сильный эффект.

Получаем эффект, мощь которого будет расти со временем.

## EffectFactory

Для более простого создания эффекта используйте EffectFactory, который на вход принимает массив параметров, например:

```php
$data = [
    'name'                  => 'Effect Name',
    'icon'                  => '/images/effects/icon.png',
    'duration'              => 10,
    'on_apply_actions'      => [
        [
            'type'           => ActionInterface::BUFF,
            'action_unit'    => $unit,
            'enemy_command'  => $enemyCommand,
            'allies_command' => $command,
            'type_target'    => ActionInterface::TARGET_SELF,
            'name'           => 'use Reserve Forces',
            'modify_method'  => 'multiplierMaxLife',
            'power'          => 130,
        ]
    ],
    'on_next_round_actions' => [],
    'on_disable_actions'    => [],
];

$effect = $this->getFactory()->create($data);
```

Это сильно проще, чем создавать эффект вручную, создавая каждый Action и ActionCollection вручную.

## Особенность Effect::getOnDisableActions()

При удалении эффекта проверяется, не было ли BuffAction при применении эффекта, и если были - необходимо откатить 
изменения характеристик юнита (что делается также, через Action).

Соответственно, даже если эффект имеет пустой `on_disable_actions`, как в примере выше, на самом деле Action при 
удалении эффекта будет создан и применен автоматически.

## Особенность анимации действий эффектов

При событиях `Effect::getOnNextRoundActions()` эффекты могут создавать те же DamageAction, что и юниты, когда наносят 
друг другу удары. Но очевидно, что урон от эффекта и урон от юнита должны анимироваться по-разному - при уроне от 
эффекта нужно просто отнять здоровье, в случае же урона от другого юнита - нужно анимировать атаку атакующего.

## Особенность механики обновления длительности эффектов

По логике обновлять длительность эффектов, и завершать их нужно в конце раунда, т.е. в $unit->newRound(), но представим
себе такую ситуацию:

- Юнит походил в текущем раунде
- Вражеский юнит оглушает его на 1 ход
- Раунд завершается, мы обновляем длительность, длительность снижается до 0 и эффект исчезает
- Начинается новый раунд, и юнит, которого оглушали спокойно ходит

Такая ситуация конечно нас не устраивает. По этому длительность эффектов обновляется после хода этого конкретного юнита,
в методе getAfterActions().

## План по реализации эффектов изменения характеристик

Реализация эффектов которые изменяют характеристики персонажа довольно трудоемкая, т.к. помимо отдельного метода на
изменение каждой характеристики нужно внимательно покрыть данный функционал тестами, предусмотрев все особые ситуации.
При этом они будут разные для увеличения и уменьшения. Чтобы отслеживать прогресс по этому процессу сделан список ниже.
Также указано название способности, где можно посмотреть пример реализации изменения данной характеристики.

- [x] `life` – increased. Пример в способности "Reserve Forces"
- [x] `life` – reduced
- [x] `mana` – increased
- [x] `mana` – reduced
- [x] `mental barrier` – increased
- [x] `mental barrier` – reduced
- [x] `damage` – increased
- [x] `damage` – reduced
- [x] `physical damage` – increased. Пример в способности "Rage"
- [x] `physical damage` – reduced
- [x] `fire damage` – increased
- [x] `fire damage` – reduced
- [x] `water damage` – increased
- [x] `water damage` – reduced
- [x] `air damage` – increased
- [x] `air damage` – reduced
- [x] `earth damage` – increased
- [x] `earth damage` – reduced
- [x] `life damage` – increased
- [x] `life damage` – reduced
- [x] `death damage` – increased
- [x] `death damage` – reduced
- [x] `physical resist` – increased
- [x] `physical resist` – reduced
- [x] `fire resist` – increased
- [x] `fire resist` – reduced
- [x] `water resist` – increased
- [x] `water resist` – reduced
- [x] `air resist` – increased
- [x] `air resist` – reduced
- [x] `earth resist` – increased
- [x] `earth resist` – reduced
- [x] `life resist` – increased
- [x] `life resist` – reduced
- [x] `death resist` – increased
- [x] `death resist` – reduced
- [x] `physical max resist` – increased
- [x] `physical max resist` – reduced
- [x] `fire max resist` – increased
- [x] `fire max resist` – reduced
- [x] `water max resist` – increased
- [x] `water max resist` – reduced
- [x] `air max resist` – increased
- [x] `air max resist` – reduced
- [x] `earth max resist` – increased
- [x] `earth max resist` – reduced
- [x] `life damage max resist` – increased
- [x] `life damage max resist` – reduced
- [x] `death damage max resist` – increased
- [x] `death damage max resist` – reduced
- [x] `accuracy` – increased
- [x] `accuracy` – reduced
- [x] `magic accuracy` – increased
- [x] `magic accuracy` – reduced
- [x] `defense` – increased
- [x] `defense` – reduced
- [x] `magic defense` – increased
- [x] `magic defense` – reduced
- [x] `critical chance` – increased
- [x] `critical chance` – reduced
- [x] `critical multiplier` – increased
- [x] `critical multiplier` – reduced
- [x] `attack speed` – increased. Пример в способности "Battle Fury"
- [x] `attack speed` – reduced
- [x] `cast speed` – increased
- [x] `cast speed` – reduced
- [x] `block` – increased. Пример в способности "Blessed Shield"
- [x] `block` – reduced
- [x] `magic block` – increased
- [x] `magic block` – reduced
- [x] `block ignore` – increased
- [x] `block ignore` – reduced
- [x] `add concentration` – increased
- [x] `add concentration` – reduced
- [x] `add cunning` – increased
- [x] `add cunning` – reduced
- [x] `add rage` – increased
- [x] `add rage` – reduced
- [x] `global damage resist` – increased
- [x] `global damage resist` – reduced
- [x] `vampirism` – increased
- [x] `vampirism` – reduced
- [x] `magic vampirism` – increased
- [x] `magic vampirism` – reduced
