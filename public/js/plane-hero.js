/**
 * Hello Passenger — 3D plane scroll hero (ES module)
 * Three.js + GSAP ScrollTrigger. Plane only.
 */
import * as THREE from 'three';
import { OBJLoader } from 'three/addons/loaders/OBJLoader.js';

var PLANE_OBJ_URL = 'https://assets.codepen.io/557388/1405+Plane_1.obj';
var sectionDuration = 1;
var tau = Math.PI * 2;

function Scene(model) {
  this.renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
  this.renderer.setSize(window.innerWidth, window.innerHeight);
  this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  this.renderer.setClearColor(0x000000, 0);

  var container = document.getElementById('hp-plane-hero');
  if (container) container.appendChild(this.renderer.domElement);

  this.scene = new THREE.Scene();
  this.camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 1, 2000);
  this.camera.position.set(0, 0, 180);
  this.camera.lookAt(0, 5, 0);

  // Éclairage pour le jaune accent
  this.light = new THREE.PointLight(0xffffff, 1.2);
  this.light.position.set(70, -20, 150);
  this.scene.add(this.light);
  this.scene.add(new THREE.AmbientLight(0xfff8f0, 2.2));

  // Couleur de l'avion : jaune du front (#F9C52D — hp-hublot-accent)
  model.traverse(function (child) {
    if (child.isMesh) {
      child.material = new THREE.MeshPhongMaterial({
        color: 0xF9C52D,
        specular: 0xfff0b0,
        shininess: 25,
        flatShading: true
      });
    }
  });

  this.modelGroup = new THREE.Group();
  this.modelGroup.add(model);
  // Texte "Hello Passenger" sur l'avion (texture canvas)
  var labelCanvas = document.createElement('canvas');
  var ctx = labelCanvas.getContext('2d');
  var text = 'Hello Passenger';
  labelCanvas.width = 512;
  labelCanvas.height = 128;
  ctx.clearRect(0, 0, labelCanvas.width, labelCanvas.height);
  ctx.font = 'bold 56px Arial, sans-serif';
  ctx.textAlign = 'center';
  ctx.textBaseline = 'middle';
  ctx.fillStyle = '#000';
  ctx.strokeStyle = 'rgba(255,255,255,0.6)';
  ctx.lineWidth = 2;
  ctx.strokeText(text, 256, 64);
  ctx.fillText(text, 256, 64);
  var labelTexture = new THREE.CanvasTexture(labelCanvas);
  labelTexture.needsUpdate = true;
  var labelGeom = new THREE.PlaneGeometry(28, 7);
  var labelMat = new THREE.MeshBasicMaterial({
    map: labelTexture,
    transparent: true,
    side: THREE.DoubleSide,
    depthWrite: false
  });
  // Dessus (toit du fuselage)
  var labelTop = new THREE.Mesh(labelGeom.clone(), labelMat.clone());
  labelTop.position.set(0.5, 0.8, -1.2);
  labelTop.rotation.x = -Math.PI * 0.35;
  labelTop.rotation.y = 0;
  this.modelGroup.add(labelTop);
  // Flanc gauche (visible depuis la gauche)
  var labelLeft = new THREE.Mesh(labelGeom.clone(), labelMat.clone());
  labelLeft.position.set(-14, 0, -0.5);
  labelLeft.rotation.x = 0;
  labelLeft.rotation.y = Math.PI / 2;
  this.modelGroup.add(labelLeft);
  // Flanc droit (visible depuis la droite)
  var labelRight = new THREE.Mesh(labelGeom.clone(), labelMat.clone());
  labelRight.position.set(14, 0, -0.5);
  labelRight.rotation.x = 0;
  labelRight.rotation.y = -Math.PI / 2;
  this.modelGroup.add(labelRight);
  // Dessous (ventre) pour quand l'avion se retourne
  var labelBottom = new THREE.Mesh(labelGeom.clone(), labelMat.clone());
  labelBottom.position.set(0.5, -0.8, -1.2);
  labelBottom.rotation.x = Math.PI * 0.35;
  labelBottom.rotation.y = 0;
  this.modelGroup.add(labelBottom);
  this.scene.add(this.modelGroup);
  this.plane = this.modelGroup;

  this.w = window.innerWidth;
  this.h = window.innerHeight;
  var self = this;
  function onResize() {
    self.w = window.innerWidth;
    self.h = window.innerHeight;
    self.camera.aspect = self.w / self.h;
    self.camera.updateProjectionMatrix();
    self.renderer.setSize(self.w, self.h);
    var pr = self.w < 768 ? 1 : Math.min(2, window.devicePixelRatio || 1);
    self.renderer.setPixelRatio(pr);
    // Responsive: scale plane and camera distance by viewport width
    var w = self.w;
    var scale = Math.max(0.35, Math.min(1, w / 1024));
    var camZ = Math.max(160, Math.min(220, 140 + (w / 1024) * 80));
    self.plane.scale.setScalar(scale);
    self.camera.position.z = camZ;
    self.camera.lookAt(0, 5, 0);
    self.render();
  }
  onResize();
  window.addEventListener('resize', onResize);
}

Scene.prototype.render = function () {
  this.renderer.render(this.scene, this.camera);
};

function loadModel(gsap, ScrollTrigger) {
  var loadingEl = document.querySelector('.hp-plane-loading');
  var manager = new THREE.LoadingManager(function () {
    if (loadingEl) loadingEl.classList.add('hp-plane-loaded');
  });
  var loader = new OBJLoader(manager);
  loader.load(PLANE_OBJ_URL, function (obj) {
    setupAnimation(obj, gsap, ScrollTrigger);
  });
}

function setupAnimation(model, gsap, ScrollTrigger) {
  gsap.registerPlugin(ScrollTrigger);
  var scene = new Scene(model);
  var plane = scene.plane;

  gsap.set(plane.rotation, { y: tau * -0.25 });
  gsap.set(plane.position, { x: 80, y: -32, z: -60 });
  scene.render();

  // Full-page flight: animation runs over entire scroll (top to bottom, including footer)
  var tl = gsap.timeline({
    onUpdate: function () { scene.render(); },
    scrollTrigger: {
      trigger: document.body,
      start: 'top top',
      end: 'bottom bottom',
      scrub: true,
      invalidateOnRefresh: true
    },
    defaults: { duration: sectionDuration, ease: 'power2.inOut' }
  });

  var delay = 0;
  tl.to(plane.position, { x: -10, ease: 'power1.in' }, delay);
  delay += sectionDuration;
  tl.to(plane.rotation, { x: tau * 0.25, y: 0, z: -tau * 0.05, ease: 'power1.inOut' }, delay);
  tl.to(plane.position, { x: -40, y: 0, z: -60, ease: 'power1.inOut' }, delay);
  delay += sectionDuration;
  tl.to(plane.rotation, { x: tau * 0.25, y: 0, z: tau * 0.05, ease: 'power3.inOut' }, delay);
  tl.to(plane.position, { x: 40, y: 0, z: -60, ease: 'power2.inOut' }, delay);
  delay += sectionDuration;
  tl.to(plane.rotation, { x: tau * 0.2, y: 0, z: -tau * 0.1, ease: 'power3.inOut' }, delay);
  tl.to(plane.position, { x: -40, y: 0, z: -30, ease: 'power2.inOut' }, delay);
  delay += sectionDuration;
  tl.to(plane.rotation, { x: 0, z: 0, y: tau * 0.25 }, delay);
  tl.to(plane.position, { x: 0, y: -10, z: 50 }, delay);
  delay += sectionDuration * 2;
  tl.to(plane.rotation, { x: tau * 0.25, y: tau * 0.5, z: 0, ease: 'power4.inOut' }, delay);
  tl.to(plane.position, { z: 30, ease: 'power4.inOut' }, delay);
  delay += sectionDuration;
  tl.to(plane.rotation, { x: tau * 0.25, y: tau * 0.5, z: 0, ease: 'power4.inOut' }, delay);
  tl.to(plane.position, { z: 60, x: 30, ease: 'power4.inOut' }, delay);
  delay += sectionDuration;
  tl.to(plane.rotation, { x: tau * 0.35, y: tau * 0.75, z: tau * 0.6, ease: 'power4.inOut' }, delay);
  tl.to(plane.position, { z: 100, x: 20, y: 0, ease: 'power4.inOut' }, delay);
  delay += sectionDuration;
  tl.to(plane.rotation, { x: tau * 0.15, y: tau * 0.85, z: 0, ease: 'power1.in' }, delay);
  tl.to(plane.position, { z: -150, x: 0, y: 0, ease: 'power1.inOut' }, delay);
  delay += sectionDuration;
  tl.to(plane.rotation, { x: -tau * 0.05, y: tau, z: -tau * 0.1, ease: 'none' }, delay);
  tl.to(plane.position, { x: 0, y: 30, z: 320, ease: 'power1.in' }, delay);
  tl.to(scene.light.position, { x: 0, y: 0, z: 0 }, delay);
}

function init() {
  var gsapLib = typeof gsap !== 'undefined' ? gsap : (window.gsap);
  var st = typeof ScrollTrigger !== 'undefined' ? ScrollTrigger : (window.ScrollTrigger);
  if (!gsapLib || !st) {
    var loadingEl = document.querySelector('.hp-plane-loading');
    if (loadingEl) loadingEl.classList.add('hp-plane-loaded');
    return;
  }
  loadModel(gsapLib, st);
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}
