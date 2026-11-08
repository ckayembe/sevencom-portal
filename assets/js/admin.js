document.addEventListener('click', (e) => {
  const add = e.target.closest('.add-row');
  if (add) {
    const name = add.dataset.target;
    const tpl = document.getElementById('tpl-' + name);
    document.querySelector('.rows[data-rows="' + name + '"]').appendChild(tpl.content.cloneNode(true));
  }
  const rm = e.target.closest('.remove-row');
  if (rm) rm.closest('.row-item').remove();
});
// Bloc « Membres » visible seulement pour un groupe ou une chorale
const kind = document.getElementById('kind'), members = document.getElementById('members-block');
if (kind && members) { const f = () => members.hidden = kind.value === 'solo'; kind.addEventListener('change', f); f(); }
// Planificateur de sorties : cases « fait », ajout / retrait d'étapes
document.addEventListener('change', (e) => {
  const box = e.target.closest('.done-toggle');
  if (!box) return;
  const task = box.closest('.task');
  task.querySelector('input[name="tasks[done][]"]').value = box.checked ? '1' : '0';
  task.classList.toggle('done', box.checked);
});
document.addEventListener('click', (e) => {
  if (e.target.closest('#add-task')) {
    const list = document.getElementById('tasks');
    list.appendChild(document.getElementById('tpl-task').content.cloneNode(true));
    list.lastElementChild.querySelector('input[name="tasks[label][]"]').focus();
  }
  const rm = e.target.closest('.remove-task');
  if (rm) rm.closest('.task').remove();
});
