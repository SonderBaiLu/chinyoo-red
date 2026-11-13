(() => {
    const form = document.querySelector('#ew-contact-editor');
    if (!form) return;
    const {apps, languages, avatars} = JSON.parse(document.querySelector('#ew-contact-initial').textContent);
    const groups = document.querySelector('#ew-contact-groups');
    function element(tag, text) {
        const el = document.createElement(tag);
        if (text) el.textContent = text;
        return el;
    }
    function button(text, run) {
        const el = element('button', text);
        el.type = 'button';
        el.className = 'button';
        el.addEventListener('click', run);
        return el;
    }
    function field(parent, label, value, update, required = true) {
        const wrap = element('label', label);
        wrap.style.cssText = 'display:block;margin:8px 0';
        const input = element('input');
        input.className = 'regular-text';
        input.style.cssText = 'display:block;width:100%;max-width:540px';
        input.value = value || '';
        input.required = required;
        input.addEventListener('input', () => update(input.value));
        wrap.append(input);
        parent.append(wrap);
        return input;
    }
    function translations(parent, row) {
        const box = element('details');
        box.append(element('summary','多语言名称'));
        parent.append(box);
        // PHP encodes an empty associative array as a JSON array.
        row.labels = {...row.labels};
        const label = element('label','语言 ');
        const select = element('select');
        for (const [code,name] of Object.entries(languages)) select.add(new Option(name,code));
        select.value = 'zh';
        label.append(select);
        box.append(label);
        const input = field(box, '译名（留空使用默认名称）', row.labels[select.value],
            value => { row.labels[select.value] = value; }, false);
        select.addEventListener('change', () => { input.value = row.labels[select.value] || ''; });
    }
    function appearance(parent, person) {
        const tools = element('div');
        tools.style.cssText = 'display:flex;align-items:center;flex-wrap:wrap;gap:8px 14px;margin:12px 0';
        const photo = element('img');
        photo.alt = '当前头像';
        photo.style.cssText = 'width:40px;height:40px;border-radius:50%;object-fit:cover';
        const setPhoto = (id, url) => {
            person.avatar_id = id;
            photo.hidden = !url;
            if (url) photo.src = url; else photo.removeAttribute('src');
            remove.hidden = !id;
        };
        const choose = button('选择或更换头像', () => {
            const picker = wp.media({title:'选择联系人头像', library:{type:'image'}, multiple:false, button:{text:'使用这张头像'}});
            picker.on('select', () => {
                const image = picker.state().get('selection').first().toJSON();
                setPhoto(image.id, image.sizes?.thumbnail?.url || image.url);
            });
            picker.open();
        });
        const remove = button('移除头像', () => setPhoto(0,''));
        setPhoto(person.avatar_id || 0,avatars[person.avatar_id]);
        tools.append(photo, choose, remove);
        parent.append(tools);
        for (const [key,label,fallback] of [['name_color','名称颜色','#24191a'],['detail_color','手机号 / 邮箱颜色','#62575a']]) {
            const wrap = element('label',label+' ');
            const input = element('input');
            input.type = 'color';
            input.value = person[key] || fallback;
            input.addEventListener('input',() => { person[key] = input.value; });
            wrap.append(input);
            tools.append(wrap);
        }
        tools.append(button('恢复默认颜色', () => {
            delete person.name_color;
            delete person.detail_color;
            const colors = tools.querySelectorAll('input[type=color]');
            colors[0].value = '#24191a';
            colors[1].value = '#62575a';
        }));
    }
    function addGroup(group, focus = false) {
        const card = element('fieldset');
        card.style.cssText = 'border:1px solid #c3c4c7;background:white;padding:16px 20px;margin:18px 0';
        card.append(element('legend','联系应用'));
        groups.append(card);
        const name = field(card,'应用名称',group.name,v => { group.name = v; });
        translations(card,group);
        const people = element('div');
        card.append(people);
        function addPerson(person, focusPerson = false) {
            const box = element('fieldset');
            box.style.cssText = 'border-inline-start:3px solid #c3c4c7;padding:4px 14px;margin:18px 0';
            box.append(element('legend','联系人'));
            people.append(box);
            const input = field(box,'联系人名称',person.name,v => { person.name = v; });
            const link = field(box,'联系链接',person.url,v => { person.url = v.trim(); });
            link.placeholder = 'https://…、mailto:… 或 tel:…';
            link.pattern = '(https://.+|mailto:.+|tel:.+)';
            appearance(box,person);
            translations(box,person);
            box.append(button('移除联系人',() => {
                group.contacts.splice(group.contacts.indexOf(person),1);
                box.remove();
            }));
            if (focusPerson) input.focus();
        }
        group.contacts.forEach(person => addPerson(person));
        card.append(button('添加联系人',() => {
            const person = {name:'',url:''};
            group.contacts.push(person);
            addPerson(person,true);
        }), document.createTextNode(' '), button('移除应用及其联系人',() => {
            apps.splice(apps.indexOf(group),1);
            card.remove();
        }));
        if (focus) name.focus();
    }
    apps.forEach(group => addGroup(group));
    document.querySelector('#ew-contact-add').addEventListener('click',() => {
        const group = {name:'',contacts:[]};
        apps.push(group);
        addGroup(group,true);
    });
    form.addEventListener('submit',() => {
        document.querySelector('#ew-contact-data').value = JSON.stringify(apps);
    });
    form.querySelector('[type=submit]').disabled = false;
})();
