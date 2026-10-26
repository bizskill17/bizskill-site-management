/* Phase 1 React shell: opt-in and isolated from legacy business screens. */
(function () {
  'use strict';
  const element = window.wp && window.wp.element;
  if (!element || !element.createElement || !element.useState) return;
  const h = element.createElement;
  function Header() {
    const [title, setTitle] = element.useState('Site Dashboard');
    element.useEffect(function () {
      const oldTitle = document.getElementById('bsm-page-title');
      if (!oldTitle) return;
      const update = () => setTitle(oldTitle.textContent.trim() || 'Site Dashboard');
      update();
      const observer = new MutationObserver(update);
      observer.observe(oldTitle, {childList:true, characterData:true, subtree:true});
      return () => observer.disconnect();
    }, []);
    return h('div', {className:'bsm-react-header-inner'},
      h('div', {className:'bsm-react-breadcrumb', 'aria-label':'Breadcrumb'}, 'Site Management', ' / ', title),
      h('h1', {className:'bsm-react-heading'}, title)
    );
  }
  function start() {
    const mount = document.getElementById('bsm-react-header');
    if (!mount) return;
    if (typeof element.createRoot === 'function') element.createRoot(mount).render(h(Header));
    else if (typeof element.render === 'function') element.render(h(Header), mount);
    else return;
    mount.classList.add('bsm-react-mounted');
    document.getElementById('bsm-app')?.classList.add('bsm-react-phase1');
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, {once:true});
  else start();
})();
