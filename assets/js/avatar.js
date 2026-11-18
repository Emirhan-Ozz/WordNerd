(function () {
  const SKIN = { s1: '#fde0c8', s2: '#f2c7a5', s3: '#e0a878', s4: '#c68653', s5: '#8d5a3b', s6: '#5c3a26' };
  const HAIR = { h1: '#2b1d16', h2: '#6b4430', h3: '#a0522d', h4: '#e3b35c', h5: '#c4c4cc', h6: '#d9478f' };
  const BG = { b1: '#6aaa64', b2: '#29b6f6', b3: '#b388eb', b4: '#f2a65a', b5: '#e86a6a', b6: '#5d8aa8', b7: '#f5d76e', b8: '#3a4049' };
  const INK = '#2b2b2b';

  let uid = 0;

  function outfit(id) {
    const body = 'M20 124 Q22 90 60 88 Q98 90 100 124 Z';
    switch (id) {
      case 'hoodie':
        return `<path d="${body}" fill="#7a5af5"/>
          <path d="M42 90 Q60 104 78 90 Q72 84 60 84 Q48 84 42 90 Z" fill="#6346d8"/>
          <path d="M54 98 L53 112 M66 98 L67 112" stroke="#e8e2ff" stroke-width="2" stroke-linecap="round"/>`;
      case 'leather':
        return `<path d="${body}" fill="#2b2b2b"/>
          <path d="M48 89 L60 112 L52 124 L36 124 Z M72 89 L60 112 L68 124 L84 124 Z" fill="#454545"/>
          <path d="M60 104 L60 124" stroke="#bbb" stroke-width="1.5"/>`;
      case 'suit':
        return `<path d="${body}" fill="#2f3a4a"/>
          <path d="M48 89 L60 108 L72 89 Q60 86 48 89 Z" fill="#ffffff"/>
          <path d="M57 92 L63 92 L61.5 98 L64 112 L60 116 L56 112 L58.5 98 Z" fill="#d23b3b"/>`;
      case 'cape':
        return `<path d="M14 124 Q16 84 60 82 Q104 84 106 124 Z" fill="#d23b3b"/>
          <path d="${body}" fill="#3b6fd4"/>
          <circle cx="60" cy="108" r="8" fill="#f5d76e"/>
          <path d="M56 108 L60 103 L64 108 L60 113 Z" fill="#d23b3b"/>`;
      default:
        return `<path d="${body}" fill="#3b82c4"/>
          <path d="M50 89 Q60 98 70 89" fill="none" stroke="#ffffff" stroke-width="3" stroke-linecap="round"/>`;
    }
  }

  function hairBack(id, color) {
    if (id === 'long') return `<path d="M30 52 Q30 26 60 24 Q90 26 90 52 L92 96 Q76 92 76 80 L44 80 Q44 92 28 96 Z" fill="${color}"/>`;
    return '';
  }

  function hairFront(id, color) {
    switch (id) {
      case 'short':
        return `<path d="M31 54 Q30 24 60 23 Q90 24 89 54 Q84 38 66 36 Q56 42 40 40 Q34 44 31 54 Z" fill="${color}"/>`;
      case 'long':
        return `<path d="M31 56 Q30 24 60 23 Q90 24 89 56 Q82 36 60 36 Q38 36 31 56 Z" fill="${color}"/>`;
      case 'curly':
        return [[36, 40, 9], [46, 30, 10], [60, 26, 11], [74, 30, 10], [84, 40, 9], [32, 50, 7], [88, 50, 7]]
          .map(([x, y, r]) => `<circle cx="${x}" cy="${y}" r="${r}" fill="${color}"/>`).join('');
      case 'spiky':
        return `<path d="M31 52 L30 36 L38 40 L40 24 L48 34 L54 20 L60 32 L66 20 L72 34 L80 24 L82 40 L90 36 L89 52 Q78 40 60 40 Q42 40 31 52 Z" fill="${color}"/>`;
      default:
        return '';
    }
  }

  function eyes(kind) {
    const open = (x) => `<ellipse cx="${x}" cy="56" rx="5" ry="6" fill="#fff"/><circle cx="${x + 1}" cy="57.5" r="3" fill="${INK}"/><circle cx="${x + 2}" cy="56" r="1" fill="#fff"/>`;
    const closed = (x) => `<path d="M${x - 5} 57 Q${x} 52 ${x + 5} 57" fill="none" stroke="${INK}" stroke-width="2.4" stroke-linecap="round"/>`;
    const happy = (x) => `<path d="M${x - 5} 58 Q${x} 51 ${x + 5} 58" fill="none" stroke="${INK}" stroke-width="2.6" stroke-linecap="round"/>`;
    const star = (x) => `<path d="M${x} 50 L${x + 1.8} 54.5 L${x + 6} 55 L${x + 2.8} 58 L${x + 3.8} 62.5 L${x} 60 L${x - 3.8} 62.5 L${x - 2.8} 58 L${x - 6} 55 L${x - 1.8} 54.5 Z" fill="#f5c518" stroke="#c99a00" stroke-width="0.8"/>`;
    const big = (x) => `<circle cx="${x}" cy="56" r="6.5" fill="#fff"/><circle cx="${x}" cy="56" r="3.6" fill="${INK}"/>`;
    const sad = (x) => `<ellipse cx="${x}" cy="57" rx="5" ry="5.5" fill="#fff"/><circle cx="${x}" cy="59.5" r="3" fill="${INK}"/>`;
    switch (kind) {
      case 'surprised': return big(49) + big(71);
      case 'calm': return closed(49) + closed(71);
      case 'wink': return open(49) + happy(71);
      case 'starry': return star(49) + star(71);
      case 'fire': return open(49) + open(71) +
        `<path d="M42 47 L55 50 M78 47 L65 50" stroke="${INK}" stroke-width="2.6" stroke-linecap="round"/>`;
      case 'happy': return happy(49) + happy(71);
      case 'sad': return sad(49) + sad(71) +
        `<path d="M43 47 Q48 46 54 49 M77 47 Q72 46 66 49" fill="none" stroke="${INK}" stroke-width="2" stroke-linecap="round"/>`;
      default: return open(49) + open(71);
    }
  }

  function mouth(kind) {
    switch (kind) {
      case 'surprised': return `<ellipse cx="60" cy="72" rx="4" ry="5" fill="#7a3b32"/>`;
      case 'calm': return `<path d="M54 70 Q60 74 66 70" fill="none" stroke="#7a3b32" stroke-width="2.4" stroke-linecap="round"/>`;
      case 'wink':
      case 'starry':
      case 'happy': return `<path d="M50 68 Q60 82 70 68 Z" fill="#7a3b32"/><path d="M53 69 Q60 72 67 69" fill="#fff"/>`;
      case 'fire': return `<path d="M52 71 Q62 76 69 68" fill="none" stroke="#7a3b32" stroke-width="2.6" stroke-linecap="round"/>`;
      case 'sad': return `<path d="M52 74 Q60 67 68 74" fill="none" stroke="#7a3b32" stroke-width="2.6" stroke-linecap="round"/>
        <path d="M76 62 Q78 67 76 69 Q74 67 76 62 Z" fill="#7cc3f0"/>`;
      default: return `<path d="M51 68 Q60 77 69 68" fill="none" stroke="#7a3b32" stroke-width="2.6" stroke-linecap="round"/>`;
    }
  }

  function glasses(id) {
    if (id !== 'glasses') return '';
    return `<g fill="none" stroke="${INK}" stroke-width="2.2">
      <circle cx="49" cy="56" r="8.5" fill="rgba(255,255,255,.15)"/><circle cx="71" cy="56" r="8.5" fill="rgba(255,255,255,.15)"/>
      <path d="M57.5 56 Q60 53 62.5 56 M40.5 55 L33 52 M79.5 55 L87 52"/></g>`;
  }

  function hat(id) {
    switch (id) {
      case 'beanie':
        return `<path d="M30 46 Q30 16 60 16 Q90 16 90 46 Z" fill="#d9534f"/>
          <rect x="27" y="40" width="66" height="11" rx="5.5" fill="#b94440"/>
          <circle cx="60" cy="14" r="6" fill="#fff"/>`;
      case 'headphones':
        return `<path d="M30 56 Q30 18 60 18 Q90 18 90 56" fill="none" stroke="#333" stroke-width="5" stroke-linecap="round"/>
          <rect x="22" y="48" width="12" height="20" rx="5" fill="#ff2bd6"/><rect x="86" y="48" width="12" height="20" rx="5" fill="#ff2bd6"/>`;
      case 'gradcap':
        return `<path d="M36 34 Q36 26 60 26 Q84 26 84 34 L84 38 Q60 32 36 38 Z" fill="#222"/>
          <path d="M60 12 L98 24 L60 34 L22 24 Z" fill="#2f2f2f"/>
          <path d="M60 23 L90 26 L90 40" fill="none" stroke="#f5c518" stroke-width="2"/>
          <circle cx="90" cy="42" r="3" fill="#f5c518"/>`;
      case 'crown':
        return `<path d="M36 34 L38 14 L49 26 L60 10 L71 26 L82 14 L84 34 Z" fill="#f5c518" stroke="#c99a00" stroke-width="1.5"/>
          <circle cx="60" cy="18" r="2.6" fill="#d23b3b"/><circle cx="45" cy="28" r="2.2" fill="#3b82c4"/><circle cx="75" cy="28" r="2.2" fill="#3b82c4"/>`;
      default:
        return '';
    }
  }

  function svg(a, mood) {
    const id = 'av' + (++uid);
    const face = mood === 'happy' || mood === 'sad' ? mood : a.face;
    const skin = SKIN[a.skin] || SKIN.s2;
    const hairColor = HAIR[a.hairColor] || HAIR.h1;
    return `<svg viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="avatar">
      <defs><clipPath id="${id}"><circle cx="60" cy="60" r="60"/></clipPath></defs>
      <g clip-path="url(#${id})">
        <rect width="120" height="120" fill="${BG[a.bg] || BG.b1}"/>
        ${hairBack(a.hair, hairColor)}
        ${outfit(a.outfit)}
        <rect x="53" y="76" width="14" height="14" rx="5" fill="${skin}"/>
        <circle cx="60" cy="56" r="29" fill="${skin}"/>
        <ellipse cx="40" cy="66" rx="5" ry="3.2" fill="#f08c8c" opacity=".55"/>
        <ellipse cx="80" cy="66" rx="5" ry="3.2" fill="#f08c8c" opacity=".55"/>
        ${eyes(face)}
        ${mouth(face)}
        ${hairFront(a.hair, hairColor)}
        ${glasses(a.glasses)}
        ${hat(a.hat)}
      </g>
    </svg>`;
  }

  window.WN = window.WN || {};
  WN.avatarSVG = svg;
  WN.avatarColors = { SKIN, HAIR, BG };

  WN.renderAvatars = function (root = document) {
    root.querySelectorAll('.avatar[data-avatar]').forEach(el => {
      try {
        el.innerHTML = svg(JSON.parse(el.dataset.avatar), el.dataset.mood || '');
      } catch (e) { }
    });
  };

  WN.renderAvatars();
})();
