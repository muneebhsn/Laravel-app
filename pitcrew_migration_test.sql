-- PitCrew seed data for local development

CREATE TABLE IF NOT EXISTS public.customers (
    id         SERIAL PRIMARY KEY,
    first_name VARCHAR(100),
    email      VARCHAR(100) UNIQUE,
    created_at TIMESTAMP DEFAULT now()
);

CREATE TABLE IF NOT EXISTS public.vehicles (
    id          SERIAL PRIMARY KEY,
    customer_id INTEGER REFERENCES public.customers(id),
    vin         VARCHAR(50) UNIQUE,
    make        VARCHAR(50),
    model       VARCHAR(50),
    year        INTEGER
);

CREATE TABLE IF NOT EXISTS public.repair_orders (
    id          SERIAL PRIMARY KEY,
    customer_id INTEGER REFERENCES public.customers(id),
    vehicle_id  INTEGER REFERENCES public.vehicles(id),
    description TEXT,
    status      VARCHAR(50) DEFAULT 'draft'
);

INSERT INTO public.customers (first_name, email) VALUES
    ('John', 'john@example.com'),
    ('Jane', 'jane@example.com'),
    ('Bob',  'bob@example.com');

INSERT INTO public.vehicles (customer_id, vin, make, model, year) VALUES
    (1, 'VIN123', 'Toyota', 'Camry',  2022),
    (2, 'VIN456', 'Honda',  'Civic',  2021),
    (3, 'VIN789', 'Ford',   'F-150',  2023);

INSERT INTO public.repair_orders (customer_id, vehicle_id, description, status) VALUES
    (1, 1, 'Oil change',    'completed'),
    (2, 2, 'Brake pads',    'in_progress'),
    (3, 3, 'Tire rotation', 'draft');
