
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
угодно — урон, лечене, баф и 
даже другой эффект.

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

- [ ] `side` – 
- [ ] `row` – 
- [x] `life` – increased. Пример в способности "Reserve Forces"
- [ ] `life` – reduced
- [ ] `mana` – increased
- [ ] `mana` – reduced
- [ ] `mental barrier` – increased
- [ ] `mental barrier` – reduced
- [x] `physical damage` – increased. Пример в способности "Rage"
- [ ] `physical damage` – reduced
- [ ] `fire damage` – increased
- [ ] `fire damage` – reduced
- [ ] `water damage` – increased
- [ ] `water damage` – reduced
- [ ] `air damage` – increased
- [ ] `air damage` – reduced
- [ ] `earth damage` – increased
- [ ] `earth damage` – reduced
- [ ] `life damage` – increased
- [ ] `life damage` – reduced
- [ ] `death damage` – increased
- [ ] `death damage` – reduced
- [ ] `physical resist` – increased
- [ ] `physical resist` – reduced
- [ ] `fire resist` – increased
- [ ] `fire resist` – reduced
- [ ] `water resist` – increased
- [ ] `water resist` – reduced
- [ ] `air resist` – increased
- [ ] `air resist` – reduced
- [ ] `earth resist` – increased
- [ ] `earth resist` – reduced
- [ ] `life resist` – increased
- [ ] `life resist` – reduced
- [ ] `death resist` – increased
- [ ] `death resist` – reduced
- [ ] `physical max resist` – increased
- [ ] `physical max resist` – reduced
- [ ] `fire max resist` – increased
- [ ] `fire max resist` – reduced
- [ ] `water max resist` – increased
- [ ] `water max resist` – reduced
- [ ] `air max resist` – increased
- [ ] `air max resist` – reduced
- [ ] `earth max resist` – increased
- [ ] `earth max resist` – reduced
- [ ] `life damage max resist` – increased
- [ ] `life damage max resist` – reduced
- [ ] `death damage max resist` – increased
- [ ] `death damage max resist` – reduced
- [x] `accuracy` – increased
- [x] `accuracy` – reduced
- [x] `magic accuracy` – increased
- [x] `magic accuracy` – reduced
- [ ] `defense` – increased
- [ ] `defense` – reduced
- [ ] `magic defense` – increased
- [ ] `magic defense` – reduced
- [ ] `critical chance` – increased
- [ ] `critical chance` – reduced
- [ ] `critical multiplier` – increased
- [ ] `critical multiplier` – reduced
- [ ] `damage type` – increased
- [ ] `damage type` – reduced
- [ ] `weapon type` – increased
- [ ] `weapon type` – reduced
- [x] `attack speed` – increased. Пример в способности "Battle Fury"
- [ ] `attack speed` – reduced
- [ ] `cast speed` – increased
- [ ] `cast speed` – reduced
- [x] `block` – increased. Пример в способности "Blessed Shield"
- [ ] `block` – reduced
- [ ] `magic block` – increased
- [ ] `magic block` – reduced
- [ ] `block ignore` – increased
- [ ] `block ignore` – reduced
- [ ] `dodge` – increased
- [ ] `dodge` – reduced
- [ ] `add concentration` – increased
- [ ] `add concentration` – reduced
- [ ] `add cunning` – increased
- [ ] `add cunning` – reduced
- [ ] `add rage` – increased
- [ ] `add rage` – reduced
- [ ] `global damage resist` – increased
- [ ] `global damage resist` – reduced
- [ ] `vampire` – increased
- [ ] `vampire` – reduced
- [ ] `magic vampire` – increased
- [ ] `magic vampire` – reduced
