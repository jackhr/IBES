import { Link } from "react-router-dom";
import type { Vehicle } from "../../lib/api";
import "./Vehicles.scss";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";

type VehiclesProps = {
  vehicles: Vehicle[];
  vehiclesError: string | null;
};

function formatVehicleType(type: string): string {
  return type
    .replace(/[_-]/g, " ")
    .trim()
    .replace(/\b\w/g, (char) => char.toUpperCase());
}

export default function Vehicles({ vehicles, vehiclesError }: VehiclesProps) {
  return (
    <div id="cars">
      {vehicles.map((vehicle) => (
        <Link className="car-container" key={vehicle.id} to="/reservation">
          <div className="overlay">
            <div />
          </div>
          <div className="top">
            <div className="left">
              <h2>{vehicle.name}</h2>
              <h3>{`${formatVehicleType(vehicle.type)} - USD$${vehicle.insurance}/day Insurance`}</h3>
              <div>
                <span>FROM</span>
                <span>
                  USD${vehicle.basePriceUsd}
                  <span style={{ fontSize: 15 }}>/</span>
                </span>
                <span>DAY</span>
              </div>
            </div>
            <div className="right">
              <div>
                <FontAwesomeIcon icon="user-group" aria-hidden />
                <span>{vehicle.people} Seats</span>
              </div>
              <div>
                <FontAwesomeIcon icon="suitcase-rolling" aria-hidden />
                <span>{vehicle.bags ?? 0} Bags</span>
              </div>
              <div>
                <FontAwesomeIcon icon="door-open" aria-hidden />
                <span>{vehicle.doors} Doors</span>
              </div>
              {vehicle.fourWd ? (
                <div>
                  <FontAwesomeIcon icon="mountain" aria-hidden />
                  <span>4WD</span>
                </div>
              ) : null}
              {vehicle.ac ? (
                <div>
                  <FontAwesomeIcon icon="snowflake" aria-hidden />
                  <span>A/C</span>
                </div>
              ) : null}
            </div>
          </div>
          <div className="bottom">
            <img loading="lazy" src={`/assets/images/vehicles/${vehicle.slug}.avif`} alt={`${vehicle.name} thumbnail`} />
          </div>
          {/* {vehicle.discountDays && vehicle.discountDays > 0 ? (
            <div className="discount-text">{`${vehicle.discountDays}+ days are discounted`}</div>
          ) : null} */}
        </Link>
      ))}
      {vehiclesError ? <p>{vehiclesError}</p> : null}
    </div>
  );
}
