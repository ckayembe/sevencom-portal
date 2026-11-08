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
