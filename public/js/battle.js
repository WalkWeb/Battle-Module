
let applyBattleChanges = {

    "applyUsrEffect": function (effect) {

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

    "revertUsrEffect": function (effect) {
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
        this.applyUsrEffect(effect);
        for (let i = 0; i < effect.targets.length; i++) {
            this.applyUsrEffect(effect.targets[i]);
        }
    },

    "revertEffect": function (effect) {
        this.revertUsrEffect(effect);
        for (let i = 0; i < effect.targets.length; i++) {
            this.revertUsrEffect(effect.targets[i]);
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
        if (user.unit_cons_bar2 !== undefined) {
            unit.getElementsByClassName("unit_cons_bar2")[0].style.width = user.unit_cons_bar2 + "%";
        }
        if (user.ava !== undefined) {
            document.getElementById("ava_" + user.user_id).className = user.ava;
        }
        if (user.avas !== undefined) {
            document.getElementById("avas_" + user.user_id).className = user.avas;
        }
        if (user.unit_rage_bar2 !== undefined) {
            unit.getElementsByClassName("unit_rage_bar2")[0].style.width = user.unit_rage_bar2 + "%";
        }
        if (user.unit_effects !== undefined) {
            document.getElementById("unit_effects_" + user.user_id).innerHTML = user.unit_effects;
        }
    },

    "applySummonEffect": function (unit) {
        let content = document.getElementById(unit.summon_row);
        content.appendChild(createUnit(unit));
    },

    "revertUsrValues": function (user) {
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
        this.revertUsrValues(effect);
        for (let i = 0; i < effect.targets.length; i++) {
            this.revertUsrValues(effect.targets[i]);
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

/**
 * TODO Refactoring?..
 *
 * @param unit
 * @returns {HTMLDivElement}
 */
function createUnit(unit) {

    let create = document.createElement('div');
    create.setAttribute('align', 'center');

    let unit_main_box = document.createElement('div');
    unit_main_box.setAttribute('class', 'unit_main_box');
    unit_main_box.setAttribute('id', 'usr_' + unit.id);
    create.appendChild(unit_main_box);

    // -----------------------------------------------

    let unit_box1 = document.createElement('div');
    unit_box1.setAttribute('class', 'unit_box1');
    unit_main_box.appendChild(unit_box1);

    let unit_box2 = document.createElement('div');
    unit_box2.setAttribute('class', unit.unit_box2_class);
    unit_main_box.appendChild(unit_box2);

    // -----------------------------------------------

    let unit_box1_right = document.createElement('div');
    unit_box1_right.setAttribute('class', 'unit_box1_right');
    unit_box1.appendChild(unit_box1_right);

    let unit_box1_left = document.createElement('div');
    unit_box1_left.setAttribute('class', 'unit_box1_left');
    unit_box1.appendChild(unit_box1_left);

    // -----------------------------------------------

    let unit_box1_right2 = document.createElement('div');
    unit_box1_right2.setAttribute('class', 'unit_box1_right2');
    unit_box1_right.appendChild(unit_box1_right2);

    let unit_box1_right3 = document.createElement('div');
    unit_box1_right3.setAttribute('class', 'unit_box1_right3');
    unit_box1_right2.appendChild(unit_box1_right3);

    let unit_box1_right4 = document.createElement('div');
    unit_box1_right4.setAttribute('class', 'unit_box1_right4');
    unit_box1_right3.appendChild(unit_box1_right4);

    let unit_hp = document.createElement('div');
    unit_hp.setAttribute('class', 'unit_hp');
    unit_box1_right4.appendChild(unit_hp);

    let hp_bar_bg_ = document.createElement('div');
    hp_bar_bg_.setAttribute('class', unit.hp_bar_class);
    hp_bar_bg_.setAttribute('id', 'hp_bar_bg_' + unit.id);
    unit_hp.appendChild(hp_bar_bg_);

    let hp_bar_ = document.createElement('div');
    hp_bar_.setAttribute('class', unit.hp_bar_class2);
    hp_bar_.setAttribute('id', 'hp_bar_' + unit.id);
    hp_bar_.style.width = unit.hp_bar_width + '%';
    hp_bar_bg_.appendChild(hp_bar_);

    let unit_hp_text = document.createElement('div');
    unit_hp_text.setAttribute('class', 'unit_hp_text')
    unit_hp.appendChild(unit_hp_text);

    let unit_hp_text_span_hp = document.createElement('span');
    unit_hp_text_span_hp.setAttribute('class', 'hp');
    unit_hp_text_span_hp.innerHTML = unit.hp;
    unit_hp_text.appendChild(unit_hp_text_span_hp);

    let unit_hp_text_span_slash = document.createElement('span');
    unit_hp_text_span_slash.innerHTML = ' / ';
    unit_hp_text.appendChild(unit_hp_text_span_slash);

    let unit_hp_text_span_thp = document.createElement('span');
    unit_hp_text_span_thp.setAttribute('class', 'thp');
    unit_hp_text_span_thp.innerHTML = unit.thp;
    unit_hp_text.appendChild(unit_hp_text_span_thp);

    let unit_hp_text_add = document.createElement('div');
    unit_hp_text_add.setAttribute('class', 'unit_hp_text_add');
    unit_hp.appendChild(unit_hp_text_add);

    let unit_hp_text_add_span = document.createElement('span');
    unit_hp_text_add_span.setAttribute('class', 'recdam');
    unit_hp_text_add.appendChild(unit_hp_text_add_span);

    let unit_cons = document.createElement('div');
    unit_cons.setAttribute('class', 'unit_cons');
    unit_box1_right4.appendChild(unit_cons);

    let unit_cons_bar2 = document.createElement('div');
    unit_cons_bar2.setAttribute('class', 'unit_cons_bar2');
    unit_cons_bar2.style.width = unit.cons_bar_width + '%';
    unit_cons.appendChild(unit_cons_bar2);

    let unit_rage = document.createElement('div');
    unit_rage.setAttribute('class', 'unit_rage');
    unit_box1_right4.appendChild(unit_rage);

    let unit_rage_bar2 = document.createElement('div');
    unit_rage_bar2.setAttribute('class', 'unit_rage_bar2');
    unit_rage_bar2.style.width = unit.rage_bar_width + '%';
    unit_rage.appendChild(unit_rage_bar2);

    // -----------------------------------------------

    let unit_box1_left2 = document.createElement('div');
    unit_box1_left2.setAttribute('class', 'unit_box1_left2');
    unit_box1_left.appendChild(unit_box1_left2);

    let unit_ava = document.createElement('div');
    unit_ava.setAttribute('class', 'unit_ava');
    unit_ava.style.backgroundImage = 'url('+ unit.avatar + ')';
    unit_box1_left2.appendChild(unit_ava);

    let ava_ = document.createElement('div');
    ava_.setAttribute('id', 'ava_' + unit.id);
    ava_.setAttribute('class', 'unit_ava_blank');
    unit_ava.appendChild(ava_);

    let avas_ = document.createElement('div');
    avas_.setAttribute('id', 'avas_' + unit.id);
    avas_.setAttribute('class', 'unit_ava_blank');
    unit_ava.appendChild(avas_);

    // -----------------------------------------------

    let unit_box2_right = document.createElement('div');
    unit_box2_right.setAttribute('class', 'unit_box2_right');
    unit_box2.appendChild(unit_box2_right);

    let unit_box2_right2 = document.createElement('div');
    unit_box2_right2.setAttribute('class', 'unit_box2_right2');
    unit_box2_right.appendChild(unit_box2_right2);

    let unit_box2_right3 = document.createElement('div');
    unit_box2_right3.setAttribute('class', 'unit_box2_right3');
    unit_box2_right2.appendChild(unit_box2_right3);

    let unit_box2_right3_p = document.createElement('p');
    unit_box2_right3.appendChild(unit_box2_right3_p);

    let unit_box2_right3_span = document.createElement('span');
    unit_box2_right3_span.style.color = unit.name_color;
    unit_box2_right3_span.innerHTML = unit.name;
    unit_box2_right3_p.appendChild(unit_box2_right3_span);

    let unit_box2_left = document.createElement('div');
    unit_box2_left.setAttribute('class', 'unit_box2_left');
    unit_box2.appendChild(unit_box2_left);

    let unit_effect_content = document.createElement('div');
    unit_effect_content.setAttribute('class', 'unit_effect_content');
    unit_box2_right.appendChild(unit_effect_content);

    let unit_effects_ = document.createElement('p');
    unit_effects_.setAttribute('id', 'unit_effects_' + unit.id);
    unit_effect_content.appendChild(unit_effects_);

    let unit_icon = document.createElement('div');
    unit_icon.setAttribute('class', 'unit_icon');
    unit_box2_left.appendChild(unit_icon);

    let unit_icon_left = document.createElement('div');
    unit_icon_left.setAttribute('class', 'unit_icon_left');
    unit_icon_left.innerHTML = unit.level.toString();
    unit_icon.appendChild(unit_icon_left)

    let unit_icon_right = document.createElement('div');
    unit_icon_right.setAttribute('class', 'unit_icon_right');
    unit_icon.appendChild(unit_icon_right);

    let unit_icon_right_img = document.createElement('img');
    unit_icon_right_img.setAttribute('src', unit.icon);
    unit_icon_right_img.setAttribute('alt', '')
    unit_icon_right.appendChild(unit_icon_right_img);

    return create;
}
