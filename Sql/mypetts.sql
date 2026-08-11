-- ============================================================================
-- BASE DE DATOS: MyPetts - Sistema de Gestión Veterinaria
-- Elaborado por: Luis Miguel Triana Avila
-- Centro de Formación Agroindustrial - La Angostura
-- Basado en: Historias de Usuario HU-01 a HU-09
-- Motor: MySQL 8.0+ (InnoDB, utf8mb4)
-- ============================================================================

DROP DATABASE IF EXISTS mypetts;
CREATE DATABASE mypetts CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mypetts;

-- ============================================================================
-- HU-01 / HU-02 / HU-07: Registro, autenticación y gestión de usuarios
-- Catálogo de roles: Administrador, Veterinario, Recepcionista, DueñoMascota
-- ============================================================================
CREATE TABLE roles (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol      VARCHAR(30)     NOT NULL UNIQUE,
    descripcion     VARCHAR(150)    NULL
) ENGINE=InnoDB;

INSERT INTO roles (nombre_rol, descripcion) VALUES
('Administrador', 'Gestiona usuarios, reportes y estadísticas del sistema'),
('Veterinario', 'Registra historial clínico, vacunas y atiende citas'),
('Recepcionista', 'Gestiona mascotas y agenda de citas'),
('DueñoMascota', 'Registra sus mascotas y agenda sus citas');

CREATE TABLE usuarios (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    nombre              VARCHAR(120)    NOT NULL,
    correo              VARCHAR(150)    NOT NULL UNIQUE,           -- HU-01 Esc.2: correo único
    contrasena_hash     VARCHAR(255)    NOT NULL,                  -- HU-01 Esc.4: mínimo 8 caracteres alfanuméricos (validado en app)
    telefono            VARCHAR(20)     NOT NULL,
    rol_id              INT             NOT NULL,                  -- FK a catálogo de roles (HU-01/HU-02/HU-07)
    activo              TINYINT(1)      NOT NULL DEFAULT 1,        -- HU-02 Esc.3 / HU-07 Esc.4-5: activar/desactivar cuenta
    intentos_fallidos   TINYINT         NOT NULL DEFAULT 0,        -- HU-02 Esc.4: control de intentos
    bloqueado_hasta     DATETIME        NULL,                      -- HU-02 Esc.4: bloqueo temporal 5 minutos
    requiere_cambio_pwd TINYINT(1)      NOT NULL DEFAULT 0,        -- HU-07 Esc.6: obliga cambio tras restablecimiento
    creado_por          INT             NULL,                      -- HU-07: administrador que creó el usuario
    created_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuarios_rol FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE RESTRICT,
    CONSTRAINT fk_usuarios_creador FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_usuarios_rol (rol_id),
    INDEX idx_usuarios_activo (activo)
) ENGINE=InnoDB;

-- HU-02 Esc.5: recuperación de contraseña (token válido 30 minutos)
CREATE TABLE tokens_recuperacion (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id      INT             NOT NULL,
    token           VARCHAR(255)    NOT NULL UNIQUE,
    expira_en       DATETIME        NOT NULL,          -- created_at + 30 minutos
    usado           TINYINT(1)      NOT NULL DEFAULT 0,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_token_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_token_valor (token)
) ENGINE=InnoDB;

-- ============================================================================
-- HU-03: Gestión de información de las mascotas
-- ============================================================================
CREATE TABLE mascotas (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    dueno_id            INT             NOT NULL,                  -- HU-03 Esc.1: asociada al dueño
    nombre              VARCHAR(100)    NOT NULL,
    especie             VARCHAR(50)     NOT NULL,
    raza                VARCHAR(80)     NOT NULL,
    fecha_nacimiento    DATE            NOT NULL,                  -- usada para calcular edad (HU-03 Esc.4)
    sexo                ENUM('Macho','Hembra') NOT NULL,
    peso                DECIMAL(6,2)    NOT NULL,
    estado_salud        ENUM('al_dia','pendiente_atencion') NOT NULL DEFAULT 'al_dia', -- HU-03 Esc.4
    activa              TINYINT(1)      NOT NULL DEFAULT 1,        -- HU-03 Esc.7: eliminar = desactivar, conserva historial
    created_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_mascota_dueno FOREIGN KEY (dueno_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    INDEX idx_mascota_dueno (dueno_id),
    INDEX idx_mascota_activa (activa),
    INDEX idx_mascota_busqueda (nombre, especie)                   -- HU-03 Esc.6: búsqueda y filtrado
) ENGINE=InnoDB;

-- ============================================================================
-- HU-04: Agendar, consultar, reagendar y cancelar citas veterinarias
-- ============================================================================
CREATE TABLE citas (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    mascota_id          INT             NOT NULL,
    veterinario_id      INT             NOT NULL,
    agendada_por        INT             NOT NULL,                  -- dueño o recepcionista (HU-04)
    fecha               DATE            NOT NULL,
    hora                TIME            NOT NULL,
    tipo_consulta       VARCHAR(100)    NOT NULL,
    estado              ENUM('agendada','cancelada','reagendada','completada') NOT NULL DEFAULT 'agendada',
    cita_origen_id       INT            NULL,                      -- HU-04 Esc.7: referencia a la cita reagendada previa
    motivo_cancelacion  VARCHAR(255)    NULL,
    created_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cita_mascota FOREIGN KEY (mascota_id) REFERENCES mascotas(id) ON DELETE RESTRICT,
    CONSTRAINT fk_cita_veterinario FOREIGN KEY (veterinario_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    CONSTRAINT fk_cita_agendo FOREIGN KEY (agendada_por) REFERENCES usuarios(id) ON DELETE RESTRICT,
    CONSTRAINT fk_cita_origen FOREIGN KEY (cita_origen_id) REFERENCES citas(id) ON DELETE SET NULL,
    UNIQUE KEY uq_cita_horario (veterinario_id, fecha, hora),      -- HU-04 Esc.2: horario no disponible/duplicado
    INDEX idx_cita_fecha (fecha, hora),
    INDEX idx_cita_estado (estado)
) ENGINE=InnoDB;

-- ============================================================================
-- HU-05: Historial clínico, vacunas y desparasitaciones
-- ============================================================================
CREATE TABLE historial_clinico (
    id                      INT AUTO_INCREMENT PRIMARY KEY,
    mascota_id              INT             NOT NULL,
    cita_id                 INT             NULL,                  -- HU-05 Esc.1: vinculada a la cita correspondiente
    veterinario_id          INT             NOT NULL,
    motivo_consulta         VARCHAR(255)    NOT NULL,
    diagnostico             TEXT            NOT NULL,
    tratamiento             TEXT            NOT NULL,
    observaciones           TEXT            NULL,
    fecha_registro          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_ultima_edicion    DATETIME        NULL,                  -- HU-05 Esc.6: marca de tiempo de edición
    editado_por             INT             NULL,                  -- HU-05 Esc.6: usuario responsable de la edición
    CONSTRAINT fk_historial_mascota FOREIGN KEY (mascota_id) REFERENCES mascotas(id) ON DELETE RESTRICT,
    CONSTRAINT fk_historial_cita FOREIGN KEY (cita_id) REFERENCES citas(id) ON DELETE SET NULL,
    CONSTRAINT fk_historial_veterinario FOREIGN KEY (veterinario_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    CONSTRAINT fk_historial_editor FOREIGN KEY (editado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_historial_mascota (mascota_id, fecha_registro)       -- HU-05 Esc.2: orden descendente por mascota
) ENGINE=InnoDB;

-- HU-05 Esc.4-5: registro de vacunas/desparasitaciones y alertas de vencimiento
CREATE TABLE vacunas_desparasitaciones (
    id                      INT AUTO_INCREMENT PRIMARY KEY,
    mascota_id              INT             NOT NULL,
    historial_id            INT             NULL,                  -- entrada clínica donde se aplicó
    veterinario_id          INT             NOT NULL,
    tipo                    ENUM('vacuna','desparasitacion') NOT NULL,
    nombre_producto         VARCHAR(120)    NOT NULL,
    lote                    VARCHAR(60)     NOT NULL,
    fecha_aplicacion        DATE            NOT NULL,
    fecha_proxima_dosis     DATE            NOT NULL,               -- HU-05 Esc.5 / HU-06 Esc.2-3: base para alertas
    aplicada_siguiente      TINYINT(1)      NOT NULL DEFAULT 0,     -- indica si ya se registró la dosis siguiente
    created_at              DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_vacuna_mascota FOREIGN KEY (mascota_id) REFERENCES mascotas(id) ON DELETE RESTRICT,
    CONSTRAINT fk_vacuna_historial FOREIGN KEY (historial_id) REFERENCES historial_clinico(id) ON DELETE SET NULL,
    CONSTRAINT fk_vacuna_veterinario FOREIGN KEY (veterinario_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    INDEX idx_vacuna_proxima_dosis (fecha_proxima_dosis)            -- HU-05 Esc.5: consulta rápida de próximas/vencidas
) ENGINE=InnoDB;

-- ============================================================================
-- HU-06: Recordatorios automáticos (log de notificaciones enviadas/fallidas)
-- ============================================================================
CREATE TABLE notificaciones (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id          INT             NOT NULL,                  -- dueño destinatario
    tipo                ENUM('recordatorio_cita','recordatorio_vacuna','alerta_vacuna_vencida','otro') NOT NULL,
    referencia_tipo     ENUM('cita','vacuna') NULL,                -- a qué registro corresponde
    referencia_id       INT             NULL,                      -- id de la cita o vacuna relacionada
    mensaje             VARCHAR(255)    NOT NULL,
    estado              ENUM('enviado','fallido') NOT NULL,        -- HU-06 Esc.4: correo no configurado/inválido
    fecha_envio         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notificacion_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_notificacion_usuario (usuario_id, estado)
) ENGINE=InnoDB;

-- ============================================================================
-- HU-08: Reportes del sistema (citas, mascotas atendidas, vacunas aplicadas)
-- Registro de auditoría de reportes generados y exportados
-- ============================================================================
CREATE TABLE reportes_generados (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id          INT             NOT NULL,                  -- Administrador o Veterinario (HU-08)
    tipo_reporte        ENUM('citas','vacunas_aplicadas','mascotas_atendidas') NOT NULL,
    fecha_inicio        DATE            NOT NULL,
    fecha_fin           DATE            NOT NULL,
    formato_exportacion ENUM('PDF','Excel') NULL,                  -- HU-08 Esc.5
    generado_en         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reporte_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_reporte_rango (fecha_inicio, fecha_fin)
) ENGINE=InnoDB;

-- ============================================================================
-- Nota sobre HU-09 (Panel de estadísticas):
-- No requiere tabla propia; los indicadores (usuarios activos por rol,
-- mascotas registradas, citas del mes completadas vs. canceladas, vacunas
-- aplicadas y comparativos entre períodos) se calculan mediante consultas
-- agregadas sobre usuarios, mascotas, citas y vacunas_desparasitaciones.
-- Se incluyen abajo como vistas de apoyo.
-- ============================================================================

-- Vista de apoyo HU-09 Esc.1: indicadores generales
CREATE OR REPLACE VIEW vw_estadisticas_generales AS
SELECT
    (SELECT COUNT(*) FROM usuarios WHERE activo = 1) AS usuarios_activos,
    (SELECT COUNT(*) FROM mascotas WHERE activa = 1) AS mascotas_registradas,
    (SELECT COUNT(*) FROM citas WHERE estado = 'completada' AND MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())) AS citas_completadas_mes,
    (SELECT COUNT(*) FROM citas WHERE estado = 'cancelada' AND MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())) AS citas_canceladas_mes,
    (SELECT COUNT(*) FROM vacunas_desparasitaciones WHERE MONTH(fecha_aplicacion) = MONTH(CURDATE()) AND YEAR(fecha_aplicacion) = YEAR(CURDATE())) AS vacunas_aplicadas_mes;

