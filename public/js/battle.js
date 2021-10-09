
let applyBattleChanges = {

    "applyUnitEffect": function (effect) {

        let unit = document.getElementById("usr_" + effect.user_id);
        if (typeof effect.class !== "undefined") {
            unit.className = effect.class;
        }
        if (typeof effect.icon_class !== "undefined") {
            let icons_elem = unit.getElementsByClassName("icons")[0],
                curr_icon_elem = icons_elem.getElementsByClassName(effect.icon_class)[0];
            if (!curr_icon_elem) {
                let curr_icon_elem = document.createElement("div");
                curr_icon_elem.className = effect.icon_class;
                unit.getElementsByClassName("icons")[0].appendChild(curr_icon_elem);
            }
        }
    },

    "revertUnitEffect": function (effect) {
        if (effect.hasOwnProperty('user_id')) {
            let unit = document.getElementById("usr_" + effect.user_id);

            unit.className = "unit_main_box";
            if (typeof effect.icon_remove !== "undefined") {
                let icons_elem = unit.getElementsByClassName("icons")[0],
                    removed_elem = icons_elem.getElementsByClassName(effect.icon_remove)[0];
                if (removed_elem) {
                    icons_elem.removeChild(removed_elem);
                }
            }
        }
    },

    "applyEffect": function (effect) {
        this.applyUnitEffect(effect);
        for (let i = 0; i < effect.targets.length; i++) {
            this.applyUnitEffect(effect.targets[i]);
        }
    },

    "revertEffect": function (effect) {
        this.revertUnitEffect(effect);
        for (let i = 0; i < effect.targets.length; i++) {
            this.revertUnitEffect(effect.targets[i]);
        }
    },

    "applyTargetEffect": function (user) {
        let unit = document.getElementById("usr_" + user.user_id);

        if (user.thp !== undefined) {
            unit.getElementsByClassName("thp")[0].innerHTML = user.thp;
        }
        if (user.hp !== undefined) {
            unit.getElementsByClassName("hp")[0].innerHTML = user.hp;
        }
        if (user.thp !== undefined) {
            unit.getElementsByClassName("thp")[0].innerHTML = user.thp;
        }
        if (user.hp_bar_class !== undefined) {
            document.getElementById("hp_bar_bg_" + user.user_id).className = user.hp_bar_class;
        }
        if (user.hp_bar_class2 !== undefined) {
            document.getElementById("hp_bar_" + user.user_id).className = user.hp_bar_class2;
        }
        if (user.unit_hp_bar_width !== undefined) {
            document.getElementById("hp_bar_" + user.user_id).style.width = user.unit_hp_bar_width + "%";
        }
        if (user.recdam !== undefined) {
            unit.getElementsByClassName("recdam")[0].innerHTML = user.recdam;
        }
        if (unit.getElementsByClassName("unit_cons_bar2").length > 0 && user.unit_cons_bar2 !== undefined) {
            unit.getElementsByClassName("unit_cons_bar2")[0].style.width = user.unit_cons_bar2 + "%";
        }
        if (unit.getElementsByClassName("unit_rage_bar2").length > 0 && user.unit_rage_bar2 !== undefined) {
            unit.getElementsByClassName("unit_rage_bar2")[0].style.width = user.unit_rage_bar2 + "%";
        }
        if (user.ava !== undefined) {
            document.getElementById("ava_" + user.user_id).className = user.ava;
        }
        if (user.avas !== undefined) {
            document.getElementById("avas_" + user.user_id).className = user.avas;
        }

        if (user.unit_effects !== undefined) {
            document.getElementById("unit_effects_" + user.user_id).innerHTML = createEffectsView(user.unit_effects);
        }
    },

    "applySummonEffect": function (unit) {
        let content = document.getElementById(unit.summon_row);
        content.appendChild(createUnit(unit));
    },

    "revertUnitValues": function (user) {
        if (user.hasOwnProperty('user_id')) {
            let unit = document.getElementById("usr_" + user.user_id);
            unit.getElementsByClassName("recdam")[0].innerHTML = "";
            document.getElementById("ava_" + user.user_id).className = "unit_ava_blank";
        }
    },

    "applyValues": function (effect) {
        this.applyTargetEffect(effect);
        for (let i = 0; i < effect.targets.length; i++) {

            if (effect.targets[i].type === 'change') {
                this.applyTargetEffect(effect.targets[i]);
            }
            if (effect.targets[i].type === 'summon') {
                this.applySummonEffect(effect.targets[i]);
            }
        }
    },

    "revertValues": function (effect) {
        this.revertUnitValues(effect);
        for (let i = 0; i < effect.targets.length; i++) {
            this.revertUnitValues(effect.targets[i]);
        }
    },

    "openNextComment": function () {
        let comments_div = document.getElementById("comment"),
            comm = comments_div.getElementsByClassName("none")[0];
        comm.className = "view";
        comments_div.scrollTop = comments_div.scrollHeight;
    },

    "setCounters": function (step, attack) {
        if (step) {
            document.getElementById("num_step").innerHTML = step;
        }
        if (attack) {
            document.getElementById("num_attack").innerHTML = attack;
        }
    }
};

function startScenario(is_console_timers) {

    let step = 0,
        attack = 0,
        effect = 0,
        phase = 0,
        attack_counter = 1,
        is_effect = getEffectType();

    function getEffectType() {
        let rest = scenario[step][attack];
        return !(typeof rest.type !== "undefined" && rest.type === "restore");
    }

    function getNext() {
        let timer = 0;
        let is_zero_time = (is_effect && scenario[step][attack]["effects"].length === 0);
        phase++;
        if (is_effect && phase === timersInfo.phases.length) {
            phase = 0;
            effect++;
            if (!scenario[step][attack]["effects"][effect]) {
                effect = 0;
                attack++;
                attack_counter++;
                if (!scenario[step][attack]) {
                    attack = 0;
                    step++;
                    if (!scenario[step]) {
                        step = -1;
                    }
                }
            }
            if (step !== -1) {
                is_effect = getEffectType();
            }
        } else if (!is_effect && phase === timersInfo.step.length) {
            phase = 0;
            effect = 0;
            attack++;
            if (!scenario[step][attack]) {
                attack = 0;
                step++;
                if (!scenario[step]) {
                    step = -1;
                }
            }
        }
        if (is_zero_time) {
            timer = timersInfo.waiting;
        } else if (step !== -1) {
            is_effect = getEffectType();
            if (is_effect) {
                timer = timersInfo.phases[phase];
            } else {
                timer = timersInfo.step[phase];
            }
        }
        return timer;
    }

    function goPhase() {
        if (is_effect) {
            let current_effect = scenario[step][attack]["effects"][effect];

            if (current_effect) {
                switch (phase) {
                    case 0:
                        applyBattleChanges.setCounters(scenario[step][attack]["step"], scenario[step][attack]["attack"]);
                        applyBattleChanges.openNextComment();
                        applyBattleChanges.applyEffect(current_effect);
                        break;
                    case 1:
                        applyBattleChanges.applyValues(current_effect);
                        break;
                    case 2:
                        applyBattleChanges.revertEffect(current_effect);
                        break;
                    case 3:
                        applyBattleChanges.revertValues(current_effect);
                        break;
                }
            } else if (phase === 0) {
                applyBattleChanges.setCounters(scenario[step][attack]["step"], scenario[step][attack]["attack"]);
                applyBattleChanges.openNextComment();
            }
        } else {
            let current_effect = scenario[step][attack];
            applyBattleChanges.applyValues(current_effect);
        }
        let timer = getNext();
        if (step !== -1) {
            if (is_console_timers) {
                console.log("Запуск таймера с задержкой: " + timer + " МС.");
            }
            setTimeout(
                function () {
                    if (is_console_timers) {
                        let endT = new Date();
                        console.log("Таймер сработал через: " + (endT - startT) + " МС.");
                    }
                    goPhase();
                },
                timer
            );
        }
    }

    return goPhase;
}

function goScenario() {
    setTimeout(
        startScenario(),
        timersInfo.start
    );
}

let timersInfo = {
    "start": 500,
    "step": [500, 500],
    "waiting": 100,
    "phases": [500, 500, 300, 200]
};

let battle_log_view = 0;
let battle_statistic_view = 0;

function showBattleLog(button_content) {

    let battle_log_button = document.getElementById('battle_log_button');
    let battle_log = document.getElementById('battle_log');

    if (battle_log_view === 0) {
        battle_log.style.display = 'block';
        battle_log_button.innerHTML = button_content[1];
        battle_log_view = 1;
    } else {
        battle_log.style.display = 'none';
        battle_log_button.innerHTML = button_content[0];
        battle_log_view = 0;
    }
}

function showBattleStatistic(button_content) {

    let battle_statistic_button = document.getElementById('battle_statistic_button');
    let battle_statistic = document.getElementById('battle_statistic');

    if (battle_statistic_view === 0) {
        battle_statistic.style.display = 'block';
        battle_statistic_button.innerHTML = button_content[1];
        battle_statistic_view = 1;
    } else {
        battle_statistic.style.display = 'none';
        battle_statistic_button.innerHTML = button_content[0];
        battle_statistic_view = 0;
    }
}

function createElement(type, parent, className, idName) {
    let element = document.createElement(type);
    if (className) {
        element.setAttribute('class', className);
    }
    if (idName) {
        element.setAttribute('id', idName);
    }
    parent.appendChild(element);
    return element;
}

function createUnit(unit) {

    let create = document.createElement('div');
    create.setAttribute('align', 'center');

    let unit_main_box = createElement('div', create, 'unit_main_box', 'usr_' + unit.id);

    let unit_box1 = createElement('div', unit_main_box, 'unit_box1');
    let unit_box2 = createElement('div', unit_main_box, unit.unit_box2_class);

    let unit_box1_right = createElement('div', unit_box1, 'unit_box1_right');
    let unit_box1_left = createElement('div', unit_box1, 'unit_box1_left');

    let unit_box1_right2 = createElement('div', unit_box1_right, 'unit_box1_right2');
    let unit_box1_right3 = createElement('div', unit_box1_right2, 'unit_box1_right3')
    let unit_box1_right4 = createElement('div', unit_box1_right3, 'unit_box1_right4');
    let unit_hp = createElement('div', unit_box1_right4, 'unit_hp');
    let hp_bar_bg_ = createElement('div', unit_hp, unit.hp_bar_class, 'hp_bar_bg_' + unit.id);
    let hp_bar_ = createElement('div', hp_bar_bg_, unit.hp_bar_class2, 'hp_bar_' + unit.id);
    hp_bar_.style.width = unit.hp_bar_width + '%';
    let unit_hp_text = createElement('div', unit_hp, 'unit_hp_text');
    let unit_hp_text_span_hp = createElement('span', unit_hp_text, 'hp');
    unit_hp_text_span_hp.innerHTML = unit.hp;
    let unit_hp_text_span_slash = createElement('span', unit_hp_text);
    unit_hp_text_span_slash.innerHTML = ' / ';
    let unit_hp_text_span_thp = createElement('span', unit_hp_text, 'thp');
    unit_hp_text_span_thp.innerHTML = unit.thp;
    let unit_hp_text_add = createElement('div', unit_hp, 'unit_hp_text_add');
    createElement('span', unit_hp_text_add, 'recdam');
    let unit_cons = createElement('div', unit_box1_right4, 'unit_cons');
    let unit_cons_bar2 = createElement('div', unit_cons, 'unit_cons_bar2');
    unit_cons_bar2.style.width = unit.cons_bar_width + '%';
    let unit_rage = createElement('div', unit_box1_right4, 'unit_rage');
    let unit_rage_bar2 = createElement('div', unit_rage,  'unit_rage_bar2');
    unit_rage_bar2.style.width = unit.rage_bar_width + '%';

    let unit_box1_left2 = createElement('div', unit_box1_left, 'unit_box1_left2');
    let unit_ava = createElement('div', unit_box1_left2, 'unit_ava');
    unit_ava.style.backgroundImage = 'url('+ unit.avatar + ')';
    createElement('div', unit_ava, 'unit_ava_blank', 'ava_' + unit.id);
    createElement('div', unit_ava, 'unit_ava_blank', 'avas_' + unit.id);

    let unit_box2_right = createElement('div', unit_box2, 'unit_box2_right');
    let unit_box2_right2 = createElement('div', unit_box2_right, 'unit_box2_right2');
    let unit_box2_right3 = createElement('div', unit_box2_right2, 'unit_box2_right3');
    let unit_box2_right3_p = createElement('p', unit_box2_right3);
    let unit_box2_right3_span = createElement('span', unit_box2_right3_p);
    unit_box2_right3_span.style.color = unit.name_color;
    unit_box2_right3_span.innerHTML = unit.name;
    let unit_box2_left = createElement('div', unit_box2, 'unit_box2_left');
    let unit_effect_content = createElement('div', unit_box2_right, 'unit_effect_container');
    createElement('p', unit_effect_content, null, 'unit_effects_' + unit.id);
    let unit_icon = createElement('div', unit_box2_left, 'unit_icon');
    let unit_icon_left = createElement('div', unit_icon, 'unit_icon_left');
    unit_icon_left.innerHTML = unit.level.toString();
    let unit_icon_right = createElement('div', unit_icon, 'unit_icon_right');
    let unit_icon_right_img = createElement('img', unit_icon_right);
    unit_icon_right_img.setAttribute('src', unit.icon);
    unit_icon_right_img.setAttribute('alt', '');

    return create;
}

function createEffectsView(effects) {
    let html = '';

    effects.forEach(function(effect) {
        html += '<img src="' + effect.icon + '" width="22" alt="" /> <span>' + effect.duration + '</span>';
    });

    return html;
}
