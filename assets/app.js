const norm = s => s.normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase().trim();
const form = document.querySelector('#evaluation');
if (form) {
  const search = document.querySelector('#buscador'), category = document.querySelector('#categoryFilter');
  const edad = document.querySelector('#edad'), sexo = document.querySelector('#sexo'), step = document.querySelector('#symptomStep');
  const cards = [...form.querySelectorAll('.categories article')], checks = [...form.querySelectorAll('input[type=checkbox]')];
  const selectedBox = document.querySelector('#selectedSymptoms');
  function filter() {
    const term = norm(search.value); let total = 0;
    cards.forEach(card => {
      const labels = [...card.querySelectorAll('.check')];
      const allowed = !category.value || card.dataset.category === category.value;
      labels.forEach(label => { label.hidden = !allowed || !norm(label.dataset.name).includes(term); });
      const visible = labels.filter(l => !l.hidden).length;
      total += visible; card.hidden = !visible;
      card.querySelector('details').open = !!visible && (!!term || !!category.value);
    });
    document.querySelector('#empty').hidden = total > 0;
  }
  function update() {
    const selected = checks.filter(c => c.checked);
    document.querySelector('#contador').textContent = selected.length;
    document.querySelector('#submit').disabled = !selected.length || !edad.checkValidity() || !sexo.checkValidity();
    document.querySelector('#alarm').hidden = !selected.some(c => c.closest('.check').dataset.alarm === '1');
    selectedBox.replaceChildren(); selectedBox.hidden = !selected.length;
    selected.forEach(c => {
      const b = document.createElement('button'); b.type = 'button'; b.className = 'selected-chip';
      b.textContent = c.closest('.check').dataset.name + ' ×'; b.setAttribute('aria-label','Quitar '+c.closest('.check').dataset.name);
      b.addEventListener('click', () => { c.checked = false; update(); }); selectedBox.append(b);
    });
    cards.forEach(card => {
      const all = [...card.querySelectorAll('input')], n = all.filter(c => c.checked).length;
      card.querySelector('.category-count').textContent = n ? `${n} de ${all.length} seleccionados` : `${all.length} síntomas`;
    });
  }
  document.querySelector('#nextStep').addEventListener('click', () => {
    if (!edad.reportValidity() || !sexo.reportValidity()) return;
    step.hidden = false; search.focus(); update();
    const a = Number(edad.value); document.querySelector('#ageHint').textContent = a <= 12 ? 'Puedes consultar la categoría Infantil.' : a >= 65 ? 'Puedes consultar la categoría Adultos mayores.' : '';
  });
  [edad,sexo].forEach(x => x.addEventListener('input',update));
  search.addEventListener('input',filter); category.addEventListener('change',filter);
  checks.forEach(c => c.addEventListener('change',update));
  form.addEventListener('reset', () => setTimeout(() => { checks.forEach(c=>c.checked=false); edad.value=''; sexo.value=''; search.value=''; category.value=''; step.hidden=true; filter(); update(); },0));
  update();
}
const catalog = document.querySelector('#catalogSearch');
if(catalog) catalog.addEventListener('input',()=>{let n=0;document.querySelectorAll('.catalog article').forEach(a=>{a.hidden=!norm(a.dataset.name).includes(norm(catalog.value));if(!a.hidden)n++});document.querySelector('#catalogEmpty').hidden=n>0});
