// @ts-check
// Inicialización de Firebase (Firestore y Auth)
// Este script debe ser incluido al final del <body> para no bloquear el renderizado.

import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.3/firebase-app.js";
import { getAuth, signInAnonymously, signInWithCustomToken, onAuthStateChanged } from "https://www.gstatic.com/firebasejs/10.12.3/firebase-auth.js";
// CORRECCIÓN CLAVE: Se añaden 'addDoc' y 'Timestamp' a las importaciones de Firestore.
import { getFirestore, collection, doc, setDoc, getDoc, onSnapshot, addDoc, Timestamp } from "https://www.gstatic.com/firebasejs/10.12.3/firebase-firestore.js";

// --- VARIABLES GLOBALES PROPORCIONADAS POR EL ENTORNO ---
const appId = typeof __app_id !== 'undefined' ? __app_id : 'default-app-id';
const firebaseConfig = JSON.parse(typeof __firebase_config !== 'undefined' ? __firebase_config : '{}');
const initialAuthToken = typeof __initial_auth_token !== 'undefined' ? __initial_auth_token : null;

// --- EXPORTACIÓN DE SERVICIOS GLOBALES ---
/** @type {import("firebase/app").FirebaseApp} */
let app;
/** @type {import("firebase/firestore").Firestore} */
let db;
/** @type {import("firebase/auth").Auth} */
let auth;
/** @type {string | null} */
let currentUserId = null;
/** @type {boolean} */
let isAuthReady = false;

// --------------------------------------------------------
// 1. INICIALIZACIÓN Y AUTENTICACIÓN
// --------------------------------------------------------

/**
 * Inicializa Firebase y autentica al usuario.
 */
async function initializeFirebase() {
    try {
        if (!firebaseConfig || Object.keys(firebaseConfig).length === 0) {
            console.error("FIREBASE ERROR: firebaseConfig no está definido o está vacío.");
            return;
        }

        // Inicializa la aplicación y servicios
        app = initializeApp(firebaseConfig);
        db = getFirestore(app);
        auth = getAuth(app);
        
        console.log("Firebase App y Firestore inicializados.");

        // Define el listener de autenticación
        onAuthStateChanged(auth, async (user) => {
            if (user) {
                currentUserId = user.uid;
                console.log(`Usuario autenticado (UID): ${currentUserId}`);
            } else {
                currentUserId = null;
                console.log("Usuario no autenticado.");
            }
            isAuthReady = true;
            // Después de que la autenticación está lista, podemos ejecutar la lógica de la aplicación
            // Por ejemplo, cargar los datos iniciales del dashboard.
            if (typeof window.onAuthReady === 'function') {
                window.onAuthReady();
            }
        });

        // Intentar iniciar sesión con el token personalizado o de forma anónima
        if (initialAuthToken) {
            await signInWithCustomToken(auth, initialAuthToken);
            console.log("Autenticación con token personalizado exitosa.");
        } else {
            // Si no hay token, iniciamos sesión anónimamente
            await signInAnonymously(auth);
            console.log("Autenticación anónima exitosa.");
        }

    } catch (error) {
        console.error("Error durante la inicialización de Firebase o la autenticación:", error);
    }
}

// --------------------------------------------------------
// 2. FUNCIONES DE BASE DE DATOS (UTILITY)
// --------------------------------------------------------

/**
 * Obtiene la ruta base para colecciones públicas.
 * @param {string} collectionName
 * @returns {import("firebase/firestore").CollectionReference}
 */
function getPublicCollection(collectionName) {
    if (!db) throw new Error("Firestore no está inicializado.");
    // Ruta: /artifacts/{appId}/public/data/{collectionName}
    return collection(db, 'artifacts', appId, 'public', 'data', collectionName);
}

// Implementación de Backoff simple para reintentos de conexión
function withExponentialBackoff(fn, maxRetries = 5, delay = 1000) {
    return async function(...args) {
        for (let i = 0; i < maxRetries; i++) {
            try {
                return await fn(...args);
            } catch (error) {
                if (i === maxRetries - 1) throw error;
                await new Promise(resolve => setTimeout(resolve, delay * Math.pow(2, i)));
            }
        }
    };
}

// --------------------------------------------------------
// 3. EXPOSICIÓN DE GLOBALES (Para usar en otros scripts)
// --------------------------------------------------------

window.app = app;
window.db = db;
window.auth = auth;
window.getCurrentUserId = () => currentUserId;
window.getPublicCollection = getPublicCollection;
window.isAuthReady = () => isAuthReady;

// CORRECCIÓN CLAVE 1: Exponemos el objeto 'firebase' para que las vistas puedan acceder a utilidades
// como Timestamp usando la sintaxis a la que están acostumbradas (window.firebase.firestore.Timestamp).
window.firebase = {
    firestore: {
        Timestamp: Timestamp,
    }
};

// Exponemos las funciones utilitarias con Backoff.
window.onSnapshotWithBackoff = (ref, callback) => {
    // onSnapshot no necesita backoff para el primer intento, ya que maneja la reconexión internamente.
    // Solo lo envolvemos para mantener la consistencia si se usa con otras funciones.
    if (!db) {
        console.warn("Firestore no está listo para onSnapshot. Se intentará en 'onAuthReady'.");
        return () => {}; // Devuelve una función vacía de "unsubscribe"
    }
    return onSnapshot(ref, callback);
};

// CORRECCIÓN CLAVE 2: Añadimos la función para agregar documentos (addDoc)
window.addDocWithBackoff = withExponentialBackoff(addDoc);
window.setDocWithBackoff = withExponentialBackoff(setDoc);
window.getDocWithBackoff = withExponentialBackoff(getDoc);

// Inicia el proceso de inicialización
initializeFirebase();