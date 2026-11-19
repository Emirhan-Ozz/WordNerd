(function () {
  const SKIN = { s1: '#fde0c8', s2: '#f2c7a5', s3: '#e0a878', s4: '#c68653', s5: '#8d5a3b', s6: '#5c3a26', s7: '#f6d5c3', s8: '#a8714a' };
  const HAIR = { h1: '#2b1d16', h2: '#6b4430', h3: '#a0522d', h4: '#e3b35c', h5: '#c4c4cc', h6: '#d9478f',
                 h7: '#e8692e', h8: '#3f6fd1', h9: '#2fa86b', h10: '#f4f1ea' };
  const BG = {
    b1: ['#8fd18a', '#4f9a4a'], b2: ['#7fd6ff', '#1e88c7'], b3: ['#d4b8ff', '#8a5fd6'], b4: ['#ffc98f', '#e0823a'],
    b5: ['#ff9f9f', '#cf4d4d'], b6: ['#93b6cf', '#3f6c8c'], b7: ['#fff1a8', '#e5b93a'], b8: ['#5b6370', '#22272e'],
    sunset: ['#ffd36e', '#ff7e5f', '#c84b8a'], ocean: ['#7ef0e0', '#2193b0', '#1b3a6b'],
    aurora: ['#9cf6c5', '#00c9a7', '#845ec2'], candy: ['#fbd786', '#f7797d', '#c06cf0'],
    dots: ['#5d8aa8', '#3f6c8c', 'dots'], stripes: ['#f2a65a', '#e08a3a', 'stripes'], stars: ['#2d3250', '#141729', 'stars'],
  };
  const BG_FALLBACK = BG.b1;
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
      case 'sweater':
        return `<path d="${body}" fill="#c0504d"/>
          <path d="M26 104 H94 M24 112 H96" stroke="#f3d7c4" stroke-width="3" stroke-dasharray="4 3"/>
          <path d="M48 89 Q60 96 72 89" fill="none" stroke="#8e3533" stroke-width="5" stroke-linecap="round"/>`;
      case 'stripes':
        return `<path d="${body}" fill="#f4f1ea"/>
          <path d="M22 98 H98 M21 106 H99 M20 114 H100 M20 122 H100" stroke="#2d4c8f" stroke-width="4"/>
          <path d="M50 89 Q60 97 70 89" fill="none" stroke="#2d4c8f" stroke-width="3" stroke-linecap="round"/>`;
      case 'jersey':
        return `<path d="${body}" fill="#2fa86b"/>
          <path d="M24 100 L36 92 M96 100 L84 92" stroke="#fff" stroke-width="4"/>
          <text x="60" y="117" text-anchor="middle" font-size="16" font-weight="800" font-family="Arial" fill="#fff">5</text>
          <path d="M50 89 L60 97 L70 89" fill="none" stroke="#fff" stroke-width="3"/>`;
      default:
        return `<path d="${body}" fill="#3b82c4"/>
          <path d="M50 89 Q60 98 70 89" fill="none" stroke="#ffffff" stroke-width="3" stroke-linecap="round"/>`;
    }
  }

  function hairBack(id, color) {
    if (id === 'bob') return `<path d="M29 54 Q28 24 60 23 Q92 24 91 54 L92 78 Q84 82 78 76 L42 76 Q36 82 28 78 Z" fill="${color}"/>`;
    if (id === 'ponytail') return `<path d="M84 40 Q104 44 100 70 Q98 84 90 90 Q94 70 82 52 Z" fill="${color}"/>`;
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
      case 'bob':
        return `<path d="M31 56 Q30 24 60 23 Q90 24 89 56 Q86 40 72 37 Q60 46 44 40 Q34 44 31 56 Z" fill="${color}"/>`;
      case 'ponytail':
        return `<path d="M31 52 Q30 24 60 23 Q90 24 89 52 Q82 36 60 35 Q38 36 31 52 Z" fill="${color}"/>
          <circle cx="86" cy="42" r="4" fill="#e86a6a"/>`;
      case 'mohawk':
        return `<path d="M50 34 L52 12 L58 26 L60 8 L64 26 L70 12 L70 34 Q60 30 50 34 Z" fill="${color}"/>
          <path d="M33 50 Q40 36 50 34 L70 34 Q80 36 87 50" fill="none" stroke="${color}" stroke-width="2" opacity=".5"/>`;
      case 'buzz':
        return `<path d="M32 50 Q31 25 60 24 Q89 25 88 50 Q80 34 60 33 Q40 34 32 50 Z" fill="${color}" opacity=".85"/>`;
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
      case 'grin': return open(49) + open(71);
      case 'tongue': return happy(49) + happy(71);
      case 'sleepy': return `<path d="M44 57 H54 M66 57 H76" stroke="${INK}" stroke-width="2.6" stroke-linecap="round"/>`;
      case 'shy': return `<ellipse cx="49" cy="58" rx="4" ry="5" fill="${INK}"/><ellipse cx="71" cy="58" rx="4" ry="5" fill="${INK}"/><circle cx="50.5" cy="56" r="1.4" fill="#fff"/><circle cx="72.5" cy="56" r="1.4" fill="#fff"/>`;
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
      case 'grin': return `<path d="M48 67 H72 Q70 80 60 80 Q50 80 48 67 Z" fill="#7a3b32"/><rect x="50" y="67" width="20" height="5" fill="#fff"/>`;
      case 'tongue': return `<path d="M51 68 Q60 77 69 68" fill="none" stroke="#7a3b32" stroke-width="2.6" stroke-linecap="round"/><path d="M57 72 Q60 82 64 72 Z" fill="#e8738a"/>`;
      case 'sleepy': return `<ellipse cx="60" cy="72" rx="3" ry="2.5" fill="#7a3b32"/><text x="82" y="44" font-size="11" font-weight="700" font-family="Arial" fill="#5b7dbf">z</text><text x="89" y="35" font-size="8" font-weight="700" font-family="Arial" fill="#5b7dbf">z</text>`;
      case 'shy': return `<path d="M55 71 Q60 74 65 71" fill="none" stroke="#7a3b32" stroke-width="2.2" stroke-linecap="round"/><ellipse cx="40" cy="66" rx="7" ry="4" fill="#f07c7c" opacity=".7"/><ellipse cx="80" cy="66" rx="7" ry="4" fill="#f07c7c" opacity=".7"/>`;
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
    if (id === 'shades') return `<g><path d="M38 51 H58 L56 61 Q48 64 41 61 Z M62 51 H82 L79 61 Q72 64 64 61 Z" fill="#1e1e1e"/>
      <path d="M58 53 H62 M38 52 L32 50 M82 52 L88 50" stroke="#1e1e1e" stroke-width="2.4"/>
      <path d="M42 54 L46 54" stroke="#fff" stroke-width="1.6" opacity=".6"/></g>`;
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
      case 'cap':
        return `<path d="M31 46 Q31 20 60 20 Q89 20 89 46 Z" fill="#3b82c4"/>
          <path d="M60 41 Q88 40 104 48 Q90 52 62 49 Z" fill="#2c66a3"/>
          <circle cx="60" cy="21" r="3" fill="#2c66a3"/>`;
      case 'bow':
        return `<path d="M70 28 L84 20 L84 38 Z M70 28 L56 20 L56 38 Z" fill="#ff6fa8"/><circle cx="70" cy="29" r="4" fill="#e04f8b"/>`;
      case 'flower':
        return `<g transform="translate(80 30)">${[0, 72, 144, 216, 288].map(r => `<ellipse cx="0" cy="-6" rx="4" ry="6" fill="#ffd36e" transform="rotate(${r})"/>`).join('')}<circle r="3.6" fill="#e8692e"/></g>`;
      case 'crown':
        return `<path d="M36 34 L38 14 L49 26 L60 10 L71 26 L82 14 L84 34 Z" fill="#f5c518" stroke="#c99a00" stroke-width="1.5"/>
          <circle cx="60" cy="18" r="2.6" fill="#d23b3b"/><circle cx="45" cy="28" r="2.2" fill="#3b82c4"/><circle cx="75" cy="28" r="2.2" fill="#3b82c4"/>`;
      default:
        return '';
    }
  }

  function background(id, uidStr) {
    const b = BG[id] || BG_FALLBACK;
    const colors = b.filter(c => c.startsWith('#'));
    const pattern = b.find(c => !c.startsWith('#'));
    const stops = colors.map((c, i) => `<stop offset="${colors.length === 1 ? 0 : i / (colors.length - 1)}" stop-color="${c}"/>`).join('');
    let out = `<defs><linearGradient id="g${uidStr}" x1="0" y1="0" x2="1" y2="1">${stops}</linearGradient></defs>
      <rect width="120" height="120" fill="url(#g${uidStr})"/>`;
    if (pattern === 'dots') {
      for (let y = 8; y < 120; y += 16) for (let x = (y / 16) % 2 ? 16 : 8; x < 120; x += 16) out += `<circle cx="${x}" cy="${y}" r="2.6" fill="#fff" opacity=".28"/>`;
    } else if (pattern === 'stripes') {
      for (let x = -120; x < 120; x += 18) out += `<path d="M${x} 120 L${x + 120} 0" stroke="#fff" stroke-width="6" opacity=".2"/>`;
    } else if (pattern === 'stars') {
      [[16, 20], [98, 14], [30, 96], [104, 84], [74, 30], [12, 60], [92, 108], [52, 12]].forEach(([x, y], i) => {
        out += `<path d="M${x} ${y - 4} L${x + 1.2} ${y - 1.2} L${x + 4} ${y} L${x + 1.2} ${y + 1.2} L${x} ${y + 4} L${x - 1.2} ${y + 1.2} L${x - 4} ${y} L${x - 1.2} ${y - 1.2} Z" fill="#fff6c2" opacity="${i % 2 ? .9 : .6}"/>`;
      });
    } else {
      out += `<circle cx="96" cy="18" r="34" fill="#fff" opacity=".12"/><circle cx="14" cy="110" r="26" fill="#000" opacity=".06"/>`;
    }
    return out;
  }

  function bgCss(id) {
    const b = BG[id] || BG_FALLBACK;
    const colors = b.filter(c => c.startsWith('#'));
    return `linear-gradient(135deg, ${colors.join(', ')})`;
  }

  function svg(a, mood) {
    const id = 'av' + (++uid);
    const face = mood === 'happy' || mood === 'sad' ? mood : a.face;
    const skin = SKIN[a.skin] || SKIN.s2;
    const hairColor = HAIR[a.hairColor] || HAIR.h1;
    return `<svg viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="avatar">
      <defs><clipPath id="${id}"><circle cx="60" cy="60" r="60"/></clipPath></defs>
      <g clip-path="url(#${id})">
        ${background(a.bg, id)}
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
  WN.avatarBgCss = bgCss;

  WN.renderAvatars = function (root = document) {
    root.querySelectorAll('.avatar[data-avatar]').forEach(el => {
      try {
        el.innerHTML = svg(JSON.parse(el.dataset.avatar), el.dataset.mood || '');
      } catch (e) { }
    });
  };

  WN.renderAvatars();
})();
