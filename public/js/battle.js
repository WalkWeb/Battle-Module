
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
        let unit = document.getElementById("usr_" + effect.user_id);
        unit.className = "unit_main_box";
        if (typeof effect.icon_remove !== "undefined") {
            let icons_elem = unit.getElementsByClassName("icons")[0],
                removed_elem = icons_elem.getElementsByClassName(effect.icon_remove)[0];
            if (removed_elem) {
                icons_elem.removeChild(removed_elem);
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

    "applyUsrValues": function (user) {
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

    "revertUsrValues": function (user) {
        let unit = document.getElementById("usr_" + user.user_id);
        unit.getElementsByClassName("recdam")[0].innerHTML = "";
        document.getElementById("ava_" + user.user_id).className = "unit_ava_blank";
    },

    "applyValues": function (effect) {
        this.applyUsrValues(effect);
        for (let i = 0; i < effect.targets.length; i++) {
            this.applyUsrValues(effect.targets[i]);
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

function HideShowAnswer(elem) {
    if ($(elem).next().css("display") === "none") {
        $(elem).next().slideDown();
        $(elem).text("Скрыть подробности боя");
    } else {
        $(elem).next().slideUp();
        $(elem).text("Показать подробности боя");
    }
}
